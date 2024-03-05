<?php

namespace Comparison\Integration\Http\Controllers;

use Comparison\MembersOfComparison\Blacklisted\Entities\Blacklisted;

class BlacklistedController {


    public function find($body): void
    {
        $bl = new BlacklistedUseCase();
        echo $bl->findByName($body);
    }

    public function getAll(): void
    {
        echo 'About Page';
    }

    public function getById(int $id): void
    {
        echo 'About Page';
    }

    public function update(array $params): Blacklisted
    {
        return Blacklisted::update($params);
    }

    public function delete(array $params): bool
    {
        return Blacklisted::delete($params['id']);
    }

    public function index(): void
    {
        echo 'welcome';
    }

    public function save(array $params): false|int
    {
        return Blacklisted::create($params);
    }

    public function filter(array $params)
    {
        $groupByParams = [
            $params['group_by'][0]
        ];
        return Blacklisted::query()
            ->toSql();
    }

}
