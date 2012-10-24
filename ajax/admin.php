<?php

/**
 * ownCloud - Updater plugin
 *
 * @author Victor Dubiniuk
 * @copyright 2012 Victor Dubiniuk victor.dubiniuk@gmail.com
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 */

namespace OCA\Updater;

\OCP\JSON::checkAdminUser();
\OCP\JSON::callCheck();

// Url to download package e.g. http://download.owncloud.org/releases/owncloud-4.0.5.tar.bz2
$packageUrl = 'https://download.owncloud.com/download/community/owncloud-latest.zip';


//Package version e.g. 4.0.4
$packageVersion = '';
$updateData = \OC_Updater::check();
if (isset($updateData['version'])) {
	$packageVersion = $updateData['version'];
}
if (isset($updateData['url']) && extension_loaded('bz2')) {
	$packageUrl = $updateData['url'];
}
if (!$packageVersion) {
	\OC_Log::write(App::APP_ID, 'No OC version found in feed.', \OC_Log::ERROR);
	\OCP\JSON::error(array('msg' => 'Version not found'));
	exit();
}

try {
	$sourcePath = Downloader::getPackage($packageUrl, $packageVersion);
} catch (\Exception $e){
	\OC_Log::write(App::APP_ID, $e->getMessage(), \OC_Log::ERROR);
	\OCP\JSON::error(array('msg' => 'Unable to fetch package'));
	exit();
}

try {
	$backupPath = Backup::createBackup();
	Updater::update($sourcePath, $backupPath);
	\OCP\JSON::success(array());
} catch (\Exception $e){
	\OC_Log::write(App::APP_ID, $e->getMessage(), \OC_Log::ERROR);
	\OCP\JSON::error(array('msg' => 'Failed to create backup'));	
}

