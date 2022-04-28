<?php

declare(strict_types=1);

namespace Szagabesz\Demo\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateCmsBlockForInvoice implements DataPatchInterface
{
    private BlockFactory $blockFactory;
    private BlockRepositoryInterface $blockRepository;

    public function __construct(BlockFactory $blockFactory, BlockRepositoryInterface $blockRepository)
    {
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
    }

    /**
     * @throws LocalizedException
     */
    public function apply()
    {
        $data = [
            'title' => 'Custom message on the Invoice PDF',
            'identifier' => 'invoice-custom-message',
            'content' => 'Thank you for buying from us',
            'is_active' => 1
        ];
        $block = $this->blockFactory->create();
        $block->setData($data);
        $this->blockRepository->save($block);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
