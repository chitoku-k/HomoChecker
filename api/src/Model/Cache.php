<?php
declare(strict_types=1);

namespace HomoChecker\Model;

class Cache implements CacheInterface
{
    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function __call(string $name, array $arguments = [])
    {
        if (!preg_match('/(load|save)(.*)/', $name, $matches)) {
            throw new \LogicException('Method must start with load or save.');
        }

        [ , $feature, $identifier ] = $matches;

        return $this->$feature(
            implode(
                ':',
                array_merge(
                    array_map(
                        'strtolower',
                        preg_split('/(?=[A-Z])/', $identifier)
                    ),
                    array_splice($arguments, -1, 1)
                )
            ),
            $arguments
        );
    }

    public function load(string $key, array $arguments = [])
    {
        return $this->redis->get($key, ...$arguments);
    }

    public function save(string $key, array $arguments = [])
    {
        return $this->redis->save($key, ...$arguments);
    }
}
