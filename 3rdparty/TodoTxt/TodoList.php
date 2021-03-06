<?php

namespace TodoTxt;

require_once __DIR__ . "/Task.php";

class TodoList implements \ArrayAccess, \Countable, \SeekableIterator, \Serializable
{
    public static $lineSeparator = "\n";
    
    protected $tasks = array();
    protected $position = 0;

    public function __construct(array $tasks = null) {
        $this->rewind();
        
        if (!is_null($tasks)) {
            $this->addTasks($tasks);
        }
    }
    
    public function addTask($task) {
        if (!($task instanceof $task)) {
            $task = new Task((string) $task);
        }
        $this->tasks[] = $task;
    }
    
    public function addTasks(array $tasks) {
        foreach ($tasks as $task) {
            $this->addTask($task);
        }
    }
    
    /**
     * Parses tasks from a newline separated string
     * @param string $taskFile A newline-separated list of tasks.
     */
    public function parseTasks($taskFile) {
        foreach (explode(self::$lineSeparator, $taskFile) as $line) {
            $line = trim($line);
            if (strlen($line) > 0) {
                $this->addTask($line);
            }
        }
    }
    
    public function getTasks() {
        return $this->tasks;
    }

    /* This is the static comparing function: */
    static function cmpPI($a, $b)
    {
        $al = strtolower($a->getPriority());
        $bl = strtolower($b->getPriority());
        $al = !empty($al) ? $al : 'zzz';
        $bl = !empty($bl) ? $bl : 'zzz';
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }		
 
    static function cmpCO($a, $b)
    {
        $al = strtolower($a->getContext());
        $bl = strtolower($b->getContext());
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }		
 
    public function sortByPriorites() {
        $tasks = $this->getTasks();
        usort($tasks, array($this, "cmpPI"));
        return $tasks; 
    }
  
    public function sortByContexts() {
        $tasks = $this->getTasks();
        usort($tasks, array($this, "cmpPI"));
        return $tasks; 
    }
   
    public function sort($mode = 0) {
        return;
    }
    
    public function __toString() {
        $this->sort();
        
        $file = "";
        foreach ($this->tasks as $task) {
            $file .= $task . self::$lineSeparator;
        }
        
        return trim($file);
    }
    
    public function offsetExists($offset) {
        return isset($this->tasks[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->tasks[$offset]) ? $this->tasks[$offset] : null;
    }
    
    public function offsetSet($offset, $value) {
        $this->tasks[$offset] = $value;
    }
    
    public function offsetUnset($offset) {
        unset($this->tasks[$offset]);
    }
    
    public function serialize() {
        return serialize($this->tasks);
    }
    
    public function unserialize($tasks) {
        $this->tasks = unserialize($tasks);
    }
    
    public function seek($position) {
        $this->position = $position;
        if (!$this->valid()) {
            throw new \OutOfBoundsException("Cannot seek to position $position.");
        }
    }
    
    public function current() {
        return $this->tasks[$this->position];
    }
    
    public function key() {
        return $this->position;
    }
    
    public function next() {
        ++$this->position;
    }
    
    public function rewind() {
        $this->position = 0;
    }
    
    public function valid() {
        return isset($this->tasks[$this->position]);
    }
    
    public function count() {
        return count($this->tasks);
    }
}
