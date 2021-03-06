<?php

namespace GoogleDriveStorage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Cache;
use Symfony\Component\Filesystem\Exception\FileNotFoundException as ExceptionFileNotFoundException;

class GoogleDriveStorageAdapter implements Filesystem
{
    const DIRECTORY_CACHE_KEY = "storage_google_drive_drive_map";
    const FILES_CACHE_KEY = "storage_google_drive_files_map";

    private $directoryMap = [];
    private $service;
    private $config;

    public function __construct(GoogleDriveService $service, array $config)
    {
        $this->config = $config;
        $this->service = $service;

        $this->directoryMap = Cache::get(self::DIRECTORY_CACHE_KEY) ?? [];
    }

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path)
    {
        return in_array($path, $this->files(dirname($path)));
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get($path)
    {
        $fileId = $this->getFileId($path);
        return $this->service->files->get($fileId, array("alt" => "media"))->getBody();
    }

    /**
     * Get a resource to read the file.
     *
     * @param  string  $path
     * @return resource|null The path resource or null on failure.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function readStream($path)
    {
        return $this->get($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string|resource  $contents
     * @param  mixed  $options
     * @return bool
     */
    public function put($path, $contents, $options = [])
    {
        $fileName = basename($path);
        $dirName = dirname($path);

        try {
            $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                'name' => $fileName,
                'parents' => array($this->getDirId($dirName))
            ));

            $file = $this->service->files->create($fileMetadata, array(
                'data' => $contents,
                'uploadType' => 'multipart',
                'fields' => 'id'));

            if($file->getId()) {
                return true;
            }
        }
        catch(\Exception $e) {
            // TODO remove directory from directoryMap
        }


        return false;
    }

    /**
     * Write a new file using a stream.
     *
     * @param  string  $path
     * @param  resource  $resource
     * @param  array  $options
     * @return bool
     *
     * @throws \InvalidArgumentException If $resource is not a file handle.
     * @throws \Illuminate\Contracts\Filesystem\FileExistsException
     */
    public function writeStream($path, $resource, array $options = [])
    {
        return $this->put($path, $resource, $options);
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getVisibility($path)
    {

    }

    /**
     * Set the visibility for the given path.
     *
     * @param  string  $path
     * @param  string  $visibility
     * @return bool
     */
    public function setVisibility($path, $visibility)
    {

    }

    /**
     * Prepend to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return bool
     */
    public function prepend($path, $data)
    {
        try {
            $emptyFile = new \Google_Service_Drive_DriveFile();
            $fileId = $this->getFileId($path);
            $fileData = $this->get($path);
            $fileData = $data.$fileData;
            $this->service->files->update($fileId, $emptyFile, array(
                'data' => $fileData,
                'uploadType' => 'multipart'
            ));
            return true;
        } catch (Exception $e) {
            // Do nothing
        }
        return false;
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return bool
     */
    public function append($path, $data)
    {
        try {
            $emptyFile = new \Google_Service_Drive_DriveFile();
            $fileId = $this->getFileId($path);
            $fileData = $this->get($path);
            $fileData = $fileData.$data;
            $this->service->files->update($fileId, $emptyFile, array(
                'data' => $fileData,
                'uploadType' => 'multipart'
            ));
            return true;
        } catch (Exception $e) {
            // Do nothing
        }
        return false;
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths)
    {
        if(is_array($paths)) {
            $success = true;
            foreach($paths as $path) {
                $success = $success && $this->delete($path);
            }
            return $success;
        }

        try {
            $fileId = $this->getFileId($paths);
            return (bool)$this->service->files->delete($fileId);
        }
        catch (\Exception $e) {
            // Do nothing
        }
        return false;
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function copy($from, $to)
    {
        $copiedFile = new \Google_Service_Drive_DriveFile();
        // try {
            $copiedFile->setParents([$this->getDirId(dirname($to))]);
            $copiedFile->setName(basename($to));
            return (bool)$this->service->files->copy($this->getFileId($from), $copiedFile);
        // } catch (\Exception $e) {
            // Do nothing
        // }
        return false;
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function move($from, $to)
    {
        $copiedFile = new \Google_Service_Drive_DriveFile();
        try {
            $copiedFile->setParents([$this->getDirId(dirname($to))]);
            $copiedFile->setName(basename($to));
            return (bool)$this->service->files->copy($this->getFileId($from), $copiedFile);
        } catch (\Exception $e) {
            // Do nothing
        }
        return false;
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path)
    {
        $fileDef = $this->getFileDefinition($path);
        return $fileDef->getSize();
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path)
    {
        $fileDef = $this->getFileDefinition($path);
        return $fileDef->getModifiedDate();
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        if(is_null($directory)) {
            $directory = ".";
        }
        $fileList = array();
        $dirId = $this->getDirId($directory);
        $fileList = $this->buildFilesList($dirId);
        if($recursive) {
            foreach(array_keys($this->allDirectories($directory)) as $dirId) {
                foreach($this->buildFilesList($dirId) as $fileId => $fileName) {
                    $fileList[$fileId] = "$fileName";
                }
            }
        }

        return $fileList;
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        if(is_null($directory)) {
            $directory = ".";
        }

        $dirId = $this->getDirId($directory);
        if(is_null($dirId)) {
            throw new FileNotFoundException($directory);
        }

        $list = $this->listSubdirs($dirId);
        $dfsList = array();

        foreach($list as $dirId => $dirName) {
            if($directory !== ".") {
                $dirName = "$directory/$dirName";
            }
            $this->directoryMap[$dirName] = $dirId;
            $dfsList[$dirId] = $dirName;
            if($recursive) {
                foreach($this->directories($dirName, $recursive) as $subDirId => $subDirName) {
                    $dfsList[$subDirId] = "$subDirName";
                }
            }
        }
        $this->writeCache();

        return $dfsList;
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return $this->directories($directory, true);
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @return bool
     */
    public function makeDirectory($path)
    {
        $file = new \Google_Service_Drive_DriveFile();
        $file->setName(basename($path));
        $file->setMimeType('application/vnd.google-apps.folder');
        $file->setParents(array($this->getDirId(dirname($path))));

        $this->service->files->create($file);

        return true;
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        $dirId = $this->getDirId($directory);
        $this->service->files->delete($dirId);
    }

    private function getFileId($path) {
        $fileDef = $this->getFileDefinition($path);
        return $fileDef->getId();
    }

    private function getFileDefinition($path, $fileName = null)
    {
        if(is_null($fileName)) {
            return $this->getFileDefinition(dirname($path), basename($path));
        }

        $dirId = $this->getDirId($path);
        return $this->retrieveFileDef($dirId, $fileName);
    }

    private function getDirId($path)
    {
        if($path === ".") {
            return $this->config["root"];
        }

        if(isset($this->directoryMap[$path])) {
            return $this->directoryMap[$path];
        }

        $parentPath = dirname($path);
        $list = $this->directories($parentPath);

        foreach($list as $subDirId => $subDirName) {
            if($path === $subDirName) {
                return $subDirId;
            }
        }

        return null;
    }

    private function buildFilesList($dirId)
    {
        $list = array();

        $optParams = array(
            'q' => sprintf("'%s' in parents and mimeType != '%s'", $dirId, "application/vnd.google-apps.folder"),
        );

        $files = $this->service->files->listFiles($optParams)->getFiles();
        $reverseDirMap = array_flip($this->directoryMap);

        if(!empty($files)) {
            foreach($files as $file) {
                $filePath = $file->getName();
                if($dirId !== $this->config["root"]) {
                    $filePath = $reverseDirMap[$dirId]."/$filePath";
                }
                $list[$dirId . " " . $file->getId()] = $filePath;
            }
        }

        return $list;
    }

    private function retrieveFileDef($dirId, $fileName)
    {
        $optParams = array(
            'q' => sprintf("'%s' in parents and mimeType != '%s' and name = '%s'",
                $dirId,
                "application/vnd.google-apps.folder",
                $fileName
            ),
        );

        $files = $this->service->files->listFiles($optParams)->getFiles();
        $reverseDirMap = array_flip($this->directoryMap);

        if(empty($files)) {
            throw new ExceptionFileNotFoundException($fileName);
        }

        return $files[0];
    }

    private function listSubdirs($dirId)
    {
        $list = array();

        $optParams = array(
            'q' => sprintf("'%s' in parents and mimeType = '%s'", $dirId, "application/vnd.google-apps.folder"),
        );

        $files = $this->service->files->listFiles($optParams)->getFiles();

        if(!empty($files)) {
            foreach($files as $file) {
                $list[$file->getId()] = $file->getName();
            }
        }

        return $list;
    }

    private function writeCache()
    {
        Cache::put(self::DIRECTORY_CACHE_KEY, $this->directoryMap);
    }
}
