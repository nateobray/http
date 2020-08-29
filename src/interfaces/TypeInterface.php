<?php
namespace obray\http\interfaces;

interface TypeInterface
{
    public function contains(string $value): bool;
}