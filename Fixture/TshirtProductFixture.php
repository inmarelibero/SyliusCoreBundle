<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Bundle\CoreBundle\Fixture;

use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Kamil Kokot <kamil.kokot@lakion.com>
 */
final class TshirtProductFixture extends AbstractFixture
{
    /**
     * @var TaxonFixture
     */
    private $taxonFixture;

    /**
     * @var RepositoryInterface
     */
    private $taxonRepository;

    /**
     * @var ProductAttributeFixture
     */
    private $productAttributeFixture;

    /**
     * @var ProductOptionFixture
     */
    private $productOptionFixture;

    /**
     * @var ProductArchetypeFixture
     */
    private $productArchetypeFixture;

    /**
     * @var ProductFixture
     */
    private $productFixture;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @param TaxonFixture $taxonFixture
     * @param RepositoryInterface $taxonRepository
     * @param ProductAttributeFixture $productAttributeFixture
     * @param ProductOptionFixture $productOptionFixture
     * @param ProductArchetypeFixture $productArchetypeFixture
     * @param ProductFixture $productFixture
     */
    public function __construct(
        TaxonFixture $taxonFixture,
        RepositoryInterface $taxonRepository,
        ProductAttributeFixture $productAttributeFixture,
        ProductOptionFixture $productOptionFixture,
        ProductArchetypeFixture $productArchetypeFixture,
        ProductFixture $productFixture
    ) {
        $this->taxonFixture = $taxonFixture;
        $this->taxonRepository = $taxonRepository;
        $this->productAttributeFixture = $productAttributeFixture;
        $this->productOptionFixture = $productOptionFixture;
        $this->productArchetypeFixture = $productArchetypeFixture;
        $this->productFixture = $productFixture;

        $this->faker = \Faker\Factory::create();
        $this->optionsResolver =
            (new OptionsResolver())
                ->setRequired('amount')
                ->setAllowedTypes('amount', 'int')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tshirt_product';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $options)
    {
        $options = $this->optionsResolver->resolve($options);

        $taxons = [];
        if (null === $this->taxonRepository->findOneBy(['code' => 'CATEGORY'])) {
            $taxons[] = ['name' => 'Category', 'code' => 'CATEGORY', 'parent' => null];
        }

        if (null === $this->taxonRepository->findOneBy(['code' => 'BRAND'])) {
            $taxons[] = ['name' => 'Brand', 'code' => 'BRAND', 'parent' => null];
        }

        $this->taxonFixture->load(['custom' => array_merge($taxons, [
            ['name' => 'T-Shirts', 'code' => 'TSHIRTS', 'parent' => 'CATEGORY'],
            ['name' => 'Super Tees', 'code' => 'SUPER-TEES', 'parent' => 'BRAND'],
        ])]);

        $this->productAttributeFixture->load(['custom' => [
            ['name' => 'T-Shirt brand', 'code' => 'TSHIRT-BRAND', 'type' => TextAttributeType::TYPE],
            ['name' => 'T-Shirt collection', 'code' => 'TSHIRT-COLLECTION', 'type' => TextAttributeType::TYPE],
            ['name' => 'T-Shirt material', 'code' => 'TSHIRT-MATERIAL', 'type' => TextAttributeType::TYPE],
        ]]);

        $this->productOptionFixture->load(['custom' => [
            [
                'name' => 'T-Shirt color',
                'code' => 'TSHIRT-COLOR',
                'values' => [
                    'TSHIRT-COLOR-RED' => 'Red',
                    'TSHIRT-COLOR-BLACK' => 'Black',
                    'TSHIRT-COLOR-WHITE' => 'White',
                ],
            ],
            [
                'name' => 'T-Shirt size',
                'code' => 'TSHIRT-SIZE',
                'values' => [
                    'TSHIRT-SIZE-S' => 'S',
                    'TSHIRT-SIZE-M' => 'M',
                    'TSHIRT-SIZE-L' => 'L',
                    'TSHIRT-SIZE-XL' => 'XL',
                    'TSHIRT-SIZE-XXL' => 'XXL',
                ],
            ],
        ]]);

        $this->productArchetypeFixture->load(['custom' => [
            [
                'name' => 'T-Shirt',
                'code' => 'TSHIRT',
                'product_attributes' => ['TSHIRT-BRAND', 'TSHIRT-COLLECTION', 'TSHIRT-MATERIAL'],
                'product_options' => ['TSHIRT-COLOR', 'TSHIRT-SIZE'],
            ],
        ]]);

        $products = [];
        for ($i = 0; $i < $options['amount']; ++$i) {
            $products[] = [
                'name' => sprintf('T-Shirt "%s"', $this->faker->word),
                'code' => $this->faker->uuid,
                'main_taxon' => 'TSHIRTS',
                'product_archetype' => 'TSHIRT',
                'taxons' => ['TSHIRTS', 'SUPER-TEES'],
                'product_attributes' => [
                    'TSHIRT-BRAND' => $this->faker->randomElement(['Nike', 'Adidas', 'JKM-476 Streetwear', 'Potato', 'Centipede Wear']),
                    'TSHIRT-COLLECTION' => sprintf('Sylius %s %s', $this->faker->randomElement(['Summer', 'Winter', 'Spring', 'Autumn']), mt_rand(1995, 2012)),
                    'TSHIRT-MATERIAL' => $this->faker->randomElement(['Centipede', 'Wool', 'Centipede 10% / Wool 90%', 'Potato 100%']),
                ],
            ];
        }

        $this->productFixture->load(['custom' => $products]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode)
    {
        $optionsNode
            ->children()
                ->integerNode('amount')->isRequired()->min(0)->end()
        ;
    }
}
