<?php

namespace GoogleDriveStorage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Cache;

class GoogleDriveStorageAdapter implements Filesystem
{
    const DIRECTORY_CACHE_KEY = "storage_google_drive_drive_map";
    const FILES_CACHE_KEY = "storage_google_drive_files_map";

    private $directoryMap = [];
    private $fileMap = [];
    private $service;
    private $config;

    public function __construct(GoogleDriveService $service, array $config)
    {
        $this->config = $config;
        $this->service = $service;

        $this->directoryMap = [];
        // $this->fileMap = Cache::get(self::FILES_CACHE_KEY) ?? [];
    }

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path)
    {
        foreach($this->files() as $file) {
            if($path === $file->getName()) {
                return true;
            }
        }
        return false;
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

    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths)
    {

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

    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path)
    {

    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path)
    {

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
        $path = $directory;

        if(is_null($directory)) {
            $directory = $this->config["root"];
        }

        $list = array();

        $optParams = array(
            'q' => sprintf("'%s' in parents and mimeType != '%s'", $directory, "application/vnd.google-apps.folder"),
        );

        $files = $this->service->files->listFiles($optParams)->getFiles();

        foreach($files as $file) {
            $list[$file->getId()] = $file->getName();
        }

        if($recursive) {
            foreach(array_keys($this->directories($file->getId())) as $subDir) {
                foreach($this->directories($subDir, true) as $fileKey => $fileName) {
                    $list[$fileKey] = $subDir."/".$fileName;
                }
            }
        }

        return $list;
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
            $directory = "/";
        }

        $dirId = $this->getCurrentDirPathId($directory);

        $list = $this->buildDirectoryList($dirId);

        foreach($list as $dirId => $dirName) {
            if($directory !== "/") {
                $dirName = "$dirName";
            }
            $this->directoryMap[$dirName] = $dirId;

            if($recursive) {
                foreach($this->directories($dirName, $recursive) as $subDirId => $subDirName) {
                    $list[$subDirId] = "$dirName/$subDirName";
                }
            }
        }

        return $list;
    }

    private function getCurrentDirPathId($path)
    {
        if(isset($this->directoryMap[$path])) {
            return $this->directoryMap[$path];
        }

        if($path === "/") {
            return $this->config["root"];
        }

        $subDirs = explode("/", $path);
        $dirId = $this->config["root"];

        while(!empty($subDirs)) {
            $lastDirId = $dirId;
            $targetDir = array_shift($subDirs);
            $dirList = $this->buildDirectoryList($dirId);
            foreach($dirList as $refId => $dirName) {
                if($dirName === $targetDir) {
                    $dirId = $refId;
                }
            }
        }
        return $dirId;
    }

    private function buildDirectoryList($dirId)
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

    }

    /**
     * Recursively delete a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {

    }
}
