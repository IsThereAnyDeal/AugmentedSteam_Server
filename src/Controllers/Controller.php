<?php
namespace AugmentedSteam\Server\Controllers;

use League\Route\Http\Exception\BadRequestException;

abstract class Controller
{
    /**
     * @param array<mixed> $data
     * @return list<int>
     */
    protected function validateIntList(array $data, string $key): array {
        if (isset($data[$key])) {
            if (!is_array($data[$key]) || !array_is_list($data[$key])) {
                throw new BadRequestException();
            }
            foreach($data[$key] as $value) {
                if (!is_int($value)) {
                    throw new BadRequestException();
                }
            }
        }
        return $data[$key] ?? []; // @phpstan-ignore-line
    }
}
