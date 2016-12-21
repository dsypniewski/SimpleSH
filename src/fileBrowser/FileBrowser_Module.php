<?php

class FileBrowser_Module extends Module
{

	const MODULE_KEY = 'fileBrowser';

	protected $_groups = array();
	protected $_users = array();

	public function __construct()
	{
		$groupFile = '/etc/group';
		if (file_exists($groupFile) and is_readable($groupFile)) {
			$groupsData = file($groupFile);
			foreach ($groupsData as $groupInfo) {
				$groupInfo = explode(':', $groupInfo);
				$this->_groups[(int) $groupInfo[2]] = $groupInfo[0];
			}
		} else {
			$this->_groups[0] = '';
		}

		$usersFile = '/etc/passwd';
		if (file_exists($usersFile) and is_readable($usersFile)) {
			$usersData = file($usersFile);
			foreach ($usersData as $userInfo) {
				$userInfo = explode(':', $userInfo);
				$this->_users[(int) $userInfo[2]] = $userInfo[0];
			}
		} else {
			$this->_users[0] = '';
		}
	}

	public function getModuleData()
	{
		return array(
			'name'      => 'FileBrowser',
			'jsClass'   => 'FileBrowser',
			'moduleKey' => self::MODULE_KEY,
			'directory' => getcwd(),
		);
	}

	public function handleRequest()
	{
		if (isset($_GET['action'])) {
			if ($_GET['action'] === 'download' and isset($_GET['file'])) {
				$this->downloadFile($_GET['file']);
			}
			$result = Handler::error('Invalid action');
		} else {
			$result = $this->handleListRequest();
		}
		die($result->toJson());
	}

	protected function handleListRequest()
	{
		if (isset($_POST['directory']) and is_dir($_POST['directory'])) {
			$currentDirectory = $_POST['directory'];
		} else {
			$currentDirectory = getcwd();
		}

		$files = array();
		$dateFormat = 'Y-m-d H:i:s O';

		try {
			$iterator = new DirectoryIterator($currentDirectory);
		} catch (Exception $e) {
			return Handler::error($e->getMessage());
		}
		/** @var SplFileInfo $fileInfo */
		foreach ($iterator as $fileInfo) {
			try {
				$filePermissions = $fileInfo->getPerms();
				$fileSize = $fileInfo->getSize();
				$userId = $fileInfo->getOwner();
				$groupId = $fileInfo->getGroup();
				$creationTime = $fileInfo->getCTime();
				$accessTime = $fileInfo->getATime();
				$modificationTime = $fileInfo->getMTime();
				if (array_key_exists($userId, $this->_users)) {
					$user = $this->_users[$userId];
				} else {
					$user = '';
				}
				if (array_key_exists($groupId, $this->_groups)) {
					$group = $this->_groups[$groupId];
				} else {
					$group = '';
				}
			} catch (Exception $e) {
				$userId = 0;
				$groupId = 0;
				$filePermissions = 0;
				$fileSize = 0;
				$creationTime = 0;
				$accessTime = 0;
				$modificationTime = 0;
				$user = '';
				$group = '';
			}
			$file = array(
				'filename'          => $fileInfo->getFilename(),
				'uid'               => $userId,
				'user'              => $user,
				'gid'               => $groupId,
				'group'             => $group,
				'mode_string'       => $this->getModeString($filePermissions),
				'permissions'       => substr(sprintf('%o', $filePermissions), -4),
				'creation_time'     => @date($dateFormat, $creationTime),
				'access_time'       => @date($dateFormat, $accessTime),
				'modification_time' => @date($dateFormat, $modificationTime),
				'type'              => $this->getFileType($filePermissions),
				'size'              => $fileSize,
				'human_size'        => $this->getHumanReadableSize($fileSize),
				'path'              => realpath($fileInfo->getPathname()),
			);
			$files[] = $file;
		}
		usort($files, function ($a, $b) {
			return strcmp($a['filename'], $b['filename']);
		});

		$result = new Result();
		$result->setData('data', $files);
		$result->setData('directory', $currentDirectory);

		return $result;
	}

	protected function getModeString($permissions)
	{
		$info = $this->getFileType($permissions, true);
		$info .= (($permissions & 0400) ? 'r' : '-');
		$info .= (($permissions & 0200) ? 'w' : '-');
		$info .= (($permissions & 0100) ? (($permissions & 04000) ? 's' : 'x') : (($permissions & 04000) ? 'S' : '-'));
		$info .= (($permissions & 0040) ? 'r' : '-');
		$info .= (($permissions & 0020) ? 'w' : '-');
		$info .= (($permissions & 0010) ? (($permissions & 02000) ? 's' : 'x') : (($permissions & 02000) ? 'S' : '-'));
		$info .= (($permissions & 0004) ? 'r' : '-');
		$info .= (($permissions & 0002) ? 'w' : '-');
		$info .= (($permissions & 0001) ? (($permissions & 01000) ? 't' : 'x') : (($permissions & 01000) ? 'T' : '-'));

		return $info;
	}

	protected function getFileType($permissions, $symbol = false)
	{
		$fileType = ($permissions & 0xF000);
		$fileTypes = array(
			0xC000 => array('symbol' => 's', 'type' => 'socket'),
			0xA000 => array('symbol' => 'l', 'type' => 'link'),
			0x8000 => array('symbol' => '-', 'type' => 'file'),
			0x6000 => array('symbol' => 'b', 'type' => 'block_device'),
			0x4000 => array('symbol' => 'd', 'type' => 'directory'),
			0x2000 => array('symbol' => 'c', 'type' => 'character_device'),
			0x1000 => array('symbol' => 'p', 'type' => 'pipe'),
		);
		if (isset($fileTypes[$fileType])) {
			return $fileTypes[$fileType][($symbol ? 'symbol' : 'type')];
		}

		return $symbol ? 'u' : 'unknown';
	}

	protected function getHumanReadableSize($size, $decimalPoints = 1, $useBinary = true)
	{
		if ($useBinary) {
			$base = 1024;
			$units = array('B  ', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
		} else {
			$base = 1000;
			$units = array('B ', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		}
		if ((int) $size === 0) {
			return '0.0 ' . $units[0];
		}
		$factor = (int) floor(log($size, $base));

		return sprintf("%.{$decimalPoints}f %s", $size / pow($base, $factor), $units[$factor]);
	}

	public function getGroupName($gid)
	{
	}

	/**
	 * @param string $filePath
	 */
	protected function downloadFile($filePath)
	{
		if (!file_exists($filePath)) {
			header('HTTP/1.0 404 Not Found');
		} else {
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
			readfile($filePath);
		}
		
		die();
	}

}
