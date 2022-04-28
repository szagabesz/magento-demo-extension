<?php

declare(strict_types=1);

namespace Szagabesz\Demo\Plugin\Sales\Model\Order\Pdf\Invoice;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Model\Order\Pdf\Invoice as InvoicePdf;
use Psr\Log\LoggerInterface as MessageLogger;
use Zend_Pdf;
use Zend_Pdf_Exception;
use Zend_Pdf_Font;
use Zend_Pdf_Page;

class AddInvoicePdfCustomMessage
{
    private const CMS_BLOCK_IDENTIFIER = 'invoice-custom-message';

    private const PDF_TEXT_LINE_HEIGHT = 14;
    private const PDF_TEXT_SPLIT_LENGTH = 45;
    private const PDF_LEFT_MARGIN = 50;
    private const PDF_BOTTOM_MARGIN = 15;
    private const PDF_FONT_SIZE = 10;
    private const PDF_NEW_PAGE_TOP = 800;

    private BlockRepositoryInterface $blockRepository;
    private Filesystem $filesystem;
    private StringUtils $string;
    private MessageLogger $logger;

    public function __construct(
        BlockRepositoryInterface $blockRepository,
        Filesystem $filesystem,
        StringUtils $string,
        MessageLogger $logger
    ) {
        $this->blockRepository = $blockRepository;
        $this->filesystem = $filesystem;
        $this->string = $string;
        $this->logger = $logger;
    }

    /**
     * @throws Zend_Pdf_Exception
     */
    public function afterGetPdf(InvoicePdf $invoicePdf, Zend_Pdf $zendPdf): Zend_Pdf
    {
        $customMessageText = $this->customMessageText();
        if ($customMessageText === null) {
            return $zendPdf;
        }

        $this->injectCustomMessage($invoicePdf, $zendPdf, $customMessageText);

        return $zendPdf;
    }

    /**
     * @throws Zend_Pdf_Exception
     */
    private function setFontRegular(Zend_Pdf_Page $page): void
    {
        $fontPath = $this->filesystem->getDirectoryReadByPath(__DIR__)->getAbsolutePath('FreeSerif.ttf');
        $font = Zend_Pdf_Font::fontWithPath($fontPath);
        $page->setFont($font, self::PDF_FONT_SIZE);
    }

    private function customMessageText(): ?string
    {
        try {
            $block = $this->blockRepository->getById(self::CMS_BLOCK_IDENTIFIER);
        } catch (LocalizedException $e) {
            $this->logger->warning(
                sprintf('Missing CMS block with identifier "%s"', self::CMS_BLOCK_IDENTIFIER)
            );
            return null;
        }

        if ($block->isActive() === false) {
            return null;
        }

        return trim($block->getContent());
    }

    /**
     * @throws Zend_Pdf_Exception
     */
    private function injectCustomMessage(InvoicePdf $invoicePdf, Zend_Pdf $zendPdf, string $text): void
    {
        /** @var Zend_Pdf_Page $page */
        $page = end($zendPdf->pages);
        $x = self::PDF_LEFT_MARGIN;
        $y = $invoicePdf->y;
        $this->setFontRegular($page);

        $textChunks = $this->string->split($text, self::PDF_TEXT_SPLIT_LENGTH, true, true);
        foreach ($textChunks as $textLine) {
            $page->drawText(trim(strip_tags($textLine)), $x, $y, 'UTF-8');
            $y -= self::PDF_TEXT_LINE_HEIGHT;
            if ($y < self::PDF_BOTTOM_MARGIN) {
                $page = $zendPdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $this->setFontRegular($page);
                $zendPdf->pages[] = $page;
                $y = self::PDF_NEW_PAGE_TOP;
            }
        }
    }

}
