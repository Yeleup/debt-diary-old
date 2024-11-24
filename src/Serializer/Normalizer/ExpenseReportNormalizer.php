<?php

namespace App\Serializer\Normalizer;

use App\Service\MoneyFormatter;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ExpenseReportNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private ObjectNormalizer $normalizer, private MoneyFormatter $moneyFormatter)
    {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $data['amount'] = $this->moneyFormatter->format($object->getAmount());
        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof \App\ApiResource\ExpenseReport;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
