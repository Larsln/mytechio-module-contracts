<?php

declare(strict_types=1);

use MyTechIO\Contracts\ContractException;
use MyTechIO\Contracts\Fakes\FakeDocumentPreview;
use MyTechIO\Contracts\Tax\DocumentPreview;
use MyTechIO\Contracts\Tax\DocumentPreviewException;
use MyTechIO\Contracts\Tax\PreviewLine;

it('previews a domestic document at 19 % by default with the sums of the lines', function () {
    $preview = new FakeDocumentPreview;
    $result = $preview->preview(7, '2026-11-11', [
        new PreviewLine('3', '101.6807', 1, articleId: 501),
        new PreviewLine('7', '4.1176', 1),
        new PreviewLine('1', '50.3361', 1),
    ]);

    expect($preview)->toBeInstanceOf(DocumentPreview::class)
        ->and($result->lines)->toHaveCount(3)
        ->and($result->lines[0]->toArray())->toBe(['scenario' => 'de_domestic', 'tax_rate' => '19.00', 'net_total' => '305.0421', 'tax_amount' => '57.9580', 'gross_total' => '363.0001'])
        ->and($result->netTotal)->toBe('384.2014')
        ->and($result->taxTotal)->toBe('72.9983')
        ->and($result->grossTotal)->toBe('457.1997')
        ->and($result->payable)->toBe('457.20')
        ->and($result->taxPerRate)->toBe(['19.00' => '72.9983'])
        ->and($result->requiresTaxReview)->toBeFalse()
        ->and($result->warnings)->toBe([])
        ->and($preview->calls()[0]['contact_id'])->toBe(7)
        ->and($preview->calls()[0]['date'])->toBe('2026-11-11')
        ->and($result->toArray()['payable'])->toBe('457.20');
});

it('uses the scenario and rate set for a contact, a rate of its own per tax category, and reports warnings', function () {
    $preview = new FakeDocumentPreview;
    $preview->taxFor(8, 'eu_b2c_oss', '21');
    $preview->categoryRate(8, 2, '6.00');
    $preview->taxFor(9, 'eu_b2b_igl', '0');
    $preview->warn('USt-ID nicht geprüft.');

    $oss = $preview->preview(8, '2026-11-11', [new PreviewLine('1', '100.0000', 1), new PreviewLine('2', '10.0000', 2)]);
    $igl = $preview->preview(9, '2026-11-11', [new PreviewLine('1', '100.0000', 1)]);

    expect($oss->lines[0]->scenario)->toBe('eu_b2c_oss')
        ->and($oss->taxPerRate)->toBe(['21.00' => '21.0000', '6.00' => '1.2000'])
        ->and($oss->grossTotal)->toBe('142.2000')
        ->and($oss->requiresTaxReview)->toBeTrue()
        ->and($oss->warnings)->toBe(['USt-ID nicht geprüft.'])
        ->and($igl->taxTotal)->toBe('0.0000')
        ->and($igl->payable)->toBe('100.00')
        ->and($igl->requiresTaxReview)->toBeTrue();
});

it('throws for a contact it was told to fail for and rejects malformed line amounts', function () {
    $preview = new FakeDocumentPreview;
    $preview->failFor(7, 'Kein Steuersatz.');

    expect(fn () => $preview->preview(7, '2026-11-11', [new PreviewLine('1', '1.0000', 1)]))->toThrow(DocumentPreviewException::class, 'Kein Steuersatz.')
        ->and(fn () => new PreviewLine('1,5', '1.0000', 1))->toThrow(ContractException::class)
        ->and(fn () => new PreviewLine('1', 'abc', 1))->toThrow(ContractException::class);
});
