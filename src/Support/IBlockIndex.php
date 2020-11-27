<?php

namespace Medvinator\BxForce\Support;

use Bitrix\Iblock\PropertyIndex\Manager;
use CBitrixComponent;
use CIBlock;

class IBlockIndex
{
    /**
     * @param int $iblockId
     */
    public static function rebuild(int $iblockId): void
    {
        Manager::deleteIndex( $iblockId );
        Manager::markAsInvalid( $iblockId );
        $index = Manager::createIndexer( $iblockId );
        $index->startIndex();
        $index->continueIndex();
        $index->endIndex();
        Manager::checkAdminNotification();
        CBitrixComponent::clearComponentCache( "bitrix:catalog.smart.filter" );
        CIBlock::clearIblockTagCache( $iblockId );
    }
}
