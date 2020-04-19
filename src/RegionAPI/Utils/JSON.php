<?php

namespace RegionAPI\Utils;

class JSON {
    /** @var String */
    private $path;
    /** @var Bool */
    private $new = false;
    /** @var Mixed[] */
    private $cache = [];
    
    public function __construct(String $path, Bool $read = false) {
        $this->path = $path;

        if (!file_exists($this->path)) {
            $this->new = true;
        }

        if ($read) {
            $this->read();
        }
    }

    /**
     * @param Mixed $contents - Contents to write to JSON
     * @param Bool $prettyPrint - whether to prettyprint or not.
     * @return Bool - Whether the write was successful
     */
    public function write($contents = null, bool $prettyPrint = true): bool {
        if (!$contents) {
            $contents = $this->cache;
        }

        try {
            $cache = json_encode($contents, ($prettyPrint) ? JSON_PRETTY_PRINT : 0);
            file_put_contents($this->path, $cache);
            $this->cache = json_decode($cache, true);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * @return Mixed - returns false if file does not exist, and contents if file exists
     */
    public function read() {
        if (!file_exists($this->path)) {
            return false;
        } else {    
            $this->cache = json_decode(file_get_contents($this->path), true);
            return $this->cache;
        }
    }

    /**
     * @return Bool - Whether the file was successfully deleted.
     */
    public function delete(): Bool {
        return unlink($this->path);
    }

    /**
     * @return Array - Gets the current cached data.
     */
    public function getCache() {
        return $this->cache;
    }
}