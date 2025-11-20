<?php

namespace WezomCms\Regions\Repositories;

use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Regions\Models\Region;

class RegionsRepository extends AbstractRepository
{
	protected function query()
	{
		return Region::query();
	}
}
