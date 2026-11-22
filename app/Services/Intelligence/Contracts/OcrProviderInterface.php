<?php

namespace App\Services\Intelligence\Contracts;

interface OcrProviderInterface
{
    /**
     * Extract structured data from physical image/document file.
     *
     * @param string $absoluteFilePath
     * @param string $documentType ktp, paspor
     * @return array [
     *     'success' => bool,
     *     'data' => array,
     *     'confidence' => float,
     *     'is_readable' => bool,
     *     'notes' => string,
     *     'provider' => string
     * ]
     */
    public function extractDocumentData(string $absoluteFilePath, string $documentType): array;

    /**
     * Check if engine/provider is installed and configured.
     */
    public function isAvailable(): bool;

    /**
     * Provider identification name.
     */
    public function getProviderName(): string;
}
