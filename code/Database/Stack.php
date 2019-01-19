<?php
namespace Database;

class Stack {
    private $saveLimit;
    private $table;
    private $template;

    private $stack;
    private $count = 0;

    private $ignore = false;
    private $update = [];

    public function __construct(int $saveLimit, string $table, array $template) {
        $this->saveLimit = $saveLimit;
        $this->table = $table;
        $this->template = $template;
        $this->stack = $this->buildStack($template);
    }

    public function setIgnore(bool $ignore): self {
        $this->ignore = $ignore;
        return $this;
    }

    public function onDuplicateKeyUpdate(...$fields): self {
        $this->update = $fields;
        return $this;
    }

    private function buildStack(array $template): array {
        $stack = [];
        foreach($template as $columnName) {
            $stack[$columnName] = [];
        }
        return $stack;
    }

    private function buildQuery(): string {
        $ignore = ($this->ignore ? "IGNORE" : "");
        $update = "";
        if (!empty($this->update)) {
            $updateFields = [];
            foreach($this->update as $columnName) {
                $updateFields[] = "[$columnName]=VALUES([$columnName])";
            }
            $update = "ON DUPLICATE KEY UPDATE ".implode(", ", $updateFields);
        }
        return "INSERT $ignore INTO [{$this->table}] %m $update";
    }

    private function clearStack(): void {
        foreach($this->stack as $columnName => $data) {
            $this->stack[$columnName] = [];
        }
        $this->count = 0;
    }

    public function stack(array $data) {
        foreach($this->template as $columnName) {
            $this->stack[$columnName][] = isset($data[$columnName]) ? $data[$columnName] : null;
        }
        $this->count++;

        if ($this->count >= $this->saveLimit) {
            $this->saveStack();
        }
    }

    public function saveStack() {
        if ($this->count == 0) { return; }
        \dibi::query($this->buildQuery(), $this->stack);
        $this->clearStack();
    }
}
