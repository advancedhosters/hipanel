<?php

namespace advancedhosters\hipanel\tests\acceptance\module\finance\manager;

use Codeception\Example;
use hipanel\helpers\Url;
use hipanel\modules\finance\tests\_support\Page\price\Create as PriceCreatePage;
use hipanel\modules\finance\tests\acceptance\manager\PriceCest as BasePriceCest;
use hipanel\tests\_support\Page\IndexPage;
use hipanel\tests\_support\Page\Widget\Input\Dropdown;
use hipanel\tests\_support\Page\Widget\Input\Input;
use hipanel\tests\_support\Step\Acceptance\Manager;

/**
 * Class PriceCest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
class PriceCest extends BasePriceCest
{
    /**
     * @var string
     */
    private $templateName;

    /**
     * @var IndexPage
     */
    private $index;

    public function _before(Manager $I)
    {
        $this->index = new IndexPage($I);
        $this->ensureIHaveTestTemplate($I);
    }

    public function ensureIndexPageWorks(Manager $I)
    {
        $I->login();
        $I->needPage(Url::to('@price'));
        $I->see('Price', 'h1');
        $this->ensureICanSeeAdvancedSearchBox();
        $this->ensureICanSeeBulkBillSearchBox();
    }

    private function ensureICanSeeAdvancedSearchBox()
    {
        $this->index->containsFilters([
            new Input('Tariff plan buyer'),
            new Input('Tariff plan owner'),
            new Input('Tariff plan name'),
            new Input('Object name'),
            new Input('Group model name'),
            new Input('Model partno'),
            new Input('Price'),
            (new Dropdown('pricesearch-type'))->withItems([
                'Monthly fee',
                'Server traffic monthly fee',
                'IP addresses monthly fee',
                'Rack unit price in monthly fee',
                'Server traffic overuse 95%',
                'Domain traffic',
                'Number of domains',
                'Backup disk usage monthly fee',
                'Backup traffic monthly fee',
                'IP traffic',
                'Number of IP addresses',
                'Support time monthly fee',
                'Account traffic',
                'Number of accounts',
                'Account disk usage',
                'Biggest directory',
                'CDN disk usage',
                'Backup traffic',
                'Number of mailboxes',
                'Support time',
                'Quantity',
            ]),
            new Input('Currency'),
        ]);
    }

    private function ensureICanSeeBulkBillSearchBox()
    {
        $this->index->containsBulkButtons([
            'Update',
            'Delete',
        ]);
        $this->index->containsColumns([
            'Object',
            'Details',
            'Price',
            'Type',
            'Note',
            'Tariff plan',
        ]);
    }

    /**
     * @return array of types that should be included in template plan
     *
     * ```php
     * return [
     *     ['vCDN'],
     *     ['pCDN']
     * ];
     * ```
     */
    protected function templatePriceTypesProvider(): array
    {
        return [
            ['Model groups'],
            ['Dedicated Server'],
            ['vCDN'],
            ['pCDN']
        ];
    }

    /**
     * @dataProvider templatePriceTypesProvider
     * @param Manager $I
     * @param Example $example
     */
    public function ensureICanCreateTemplatePlan(Manager $I, Example $example): void
    {
        $page = new PriceCreatePage($I, $this->id);
        $page->openModal();
        $page->choosePriceType($example[0]);
        $page->proceedToCreation();
        $page->fillRandomPrices('templateprice');
        $page->saveForm();
        $page->seeRandomPrices();
    }

    private function ensureIHaveTestTemplate(Manager $I): void
    {
        if (!$this->templateName) {
            $this->templateName = uniqid('TemplatePlan', true);
            $this->id = $this->createPlan($I, $this->templateName, 'Template');
            $I->needPage(Url::to(['@plan/view', 'id' => $this->id]));
            $I->see('No prices found');
        }
    }

    protected function suggestedPricesOptionsProvider(Manager $I): array
    {
        return [
            [
                'type' => 'Server',
                'templateName' => $this->templateName,
                'priceTypes' => ['Main prices', 'Parts prices'],
                'object' => 'TEST01',
            ],
            [
                'type' => 'vCDN',
                'templateName' => $this->templateName,
                'priceTypes' => ['Main prices'],
                'object' => 'vCDN-soltest',
            ],
        ];
    }
}
