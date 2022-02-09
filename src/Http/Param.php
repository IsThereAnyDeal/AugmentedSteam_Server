<?php
namespace AugmentedSteam\Server\Http;

use AugmentedSteam\Server\Exceptions\InvalidValueException;
use AugmentedSteam\Server\Exceptions\MissingParameterException;
use Psr\Http\Message\ServerRequestInterface;

class Param
{
    private array $data;
    private string $name;
    private $default;
    private $hasDefault = false;

    public function __construct(ServerRequestInterface $request, string $name) {
        $this->data = $request->getQueryParams();
        $this->name = $name;
    }

    public function default($value): self {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }

    public function exists(): bool {
        return array_key_exists($this->name, $this->data);
    }

    public function isInt(): bool {
        return preg_match("#^[0-9]+$#", $this->data[$this->name]);
    }

    private function value() /* TODO php8: mixed */ {
        return $this->data[$this->name];
    }

    /**
     * @return int|null
     * @throws InvalidValueException
     * @throws MissingParameterException
     */
    public function int(): ?int {
        if ($this->exists()) {
            if ($this->isInt()) {
                return (int)$this->value();
            }

            throw new InvalidValueException($this->name);
        } elseif ($this->hasDefault) {
            return $this->default;
        }

        throw new MissingParameterException($this->name);
    }

    /**
     * @throws MissingParameterException
     * @throws InvalidValueException
     */
    public function bool(): bool {
        if ($this->exists()) {
            $value = $this->value();
            if ($value == "1" || $value == "true") {
                return true;
            } elseif ($value == "0" || $value == "false") {
                return false;
            }

            throw new InvalidValueException($this->name);
        } elseif ($this->hasDefault) {
            return $this->default;
        }

        throw new MissingParameterException($this->name);
    }

    /**
     * @throws MissingParameterException
     */
    public function string(): ?string {
        if ($this->exists()) {
            $value = trim($this->value());
            if (!empty($value)) {
                return $value;
            }
        } elseif ($this->hasDefault) {
            return $this->default;
        }

        throw new MissingParameterException($this->name);
    }

    /**
     * @throws MissingParameterException
     */
    public function list($separator=","): array {
        if ($this->exists()) {
            return explode($separator, $this->value());
        } elseif ($this->hasDefault && is_array($this->default)) {
            return $this->default;
        }

        throw new MissingParameterException($this->name);
    }
}
