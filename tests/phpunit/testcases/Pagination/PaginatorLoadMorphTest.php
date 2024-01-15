<?php

namespace Tests\Pagination;

use WPWhales\Database\Eloquent\Collection;
use WPWCore\Pagination\AbstractPaginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PaginatorLoadMorphTest extends TestCase
{
    public function testCollectionLoadMorphCanChainOnThePaginator()
    {
        $relations = [
            'App\\User' => 'photos',
            'App\\Company' => ['employees', 'calendars'],
        ];

        $items = m::mock(Collection::class);
        $items->shouldReceive('loadMorph')->once()->with('parentable', $relations);

        $p = (new class extends AbstractPaginator {})->setCollection($items);

        $this->assertSame($p, $p->loadMorph('parentable', $relations));
    }
}
