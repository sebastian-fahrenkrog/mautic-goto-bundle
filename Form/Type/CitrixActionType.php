<?php
/**
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCitrixBundle\Form\Type;

use MauticPlugin\MauticCitrixBundle\Helper\CitrixHelper;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixProducts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class FormFieldSelectType.
 */
class CitrixActionType extends AbstractType
{

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     * @throws \Symfony\Component\Validator\Exception\InvalidOptionsException
     * @throws \Symfony\Component\Validator\Exception\MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!(array_key_exists('attr', $options) && array_key_exists('data-product', $options['attr'])) ||
            !CitrixProducts::isValidValue($options['attr']['data-product']) ||
            !CitrixHelper::isAuthorized('Goto'.$options['attr']['data-product'])
        ) {
            return;
        }
        $product = $options['attr']['data-product'];

        $modelFactory = CitrixHelper::getContainer()->get('mautic.model.factory');
        /** @var \Mautic\FormBundle\Model\FieldModel $model */
        $model = $modelFactory->getModel('form.field');
        $fields = $model->getSessionFields($options['attr']['data-formid']);
        $choices = [
            '' => '',
        ];
        foreach ($fields as $f) {
            if (in_array(
                $f['type'],
                array_merge(
                    ['button', 'freetext', 'captcha'],
                    array_map(
                        function ($p) {
                            return 'plugin.citrix.select.'.$p;
                        },
                        CitrixProducts::toArray()
                    )
                ),
                true
            )) {
                continue;
            }
            $choices[$f['id']] = $f['label'];
        }

        if (array_key_exists('data-product-action', $options['attr']) &&
            ('register' === $options['attr']['data-product-action'] ||
                'start' === $options['attr']['data-product-action'])
        ) {

            $products = [
                'form' => 'User selection from form',
            ];
            $products = array_replace($products, CitrixHelper::getCitrixChoices($product));

            $builder->add(
                'product',
                'choice',
                array(
                    'choices' => $products,
                    'expanded' => false,
                    'label_attr' => array('class' => 'control-label'),
                    'multiple' => false,
                    'label' => 'plugin.citrix.'.$product.'.listfield',
                    'attr' => array(
                        'class' => 'form-control',
                        'tooltip' => 'plugin.citrix.selectproduct.tooltip',
                    ),
                    'required' => true,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                )
            );
        }

        if (array_key_exists('data-product-action', $options['attr']) &&
            ('register' === $options['attr']['data-product-action'] ||
                'screensharing' === $options['attr']['data-product-action'])
        ) {

            $builder->add(
                'firstname',
                'choice',
                array(
                    'choices' => $choices,
                    'expanded' => false,
                    'label_attr' => array('class' => 'control-label'),
                    'multiple' => false,
                    'label' => 'plugin.citrix.first_name',
                    'attr' => array(
                        'class' => 'form-control',
                        'tooltip' => 'plugin.citrix.first_name.tooltip',
                    ),
                    'required' => true,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                )
            );

            $builder->add(
                'lastname',
                'choice',
                array(
                    'choices' => $choices,
                    'expanded' => false,
                    'label_attr' => array('class' => 'control-label'),
                    'multiple' => false,
                    'label' => 'plugin.citrix.last_name',
                    'attr' => array(
                        'class' => 'form-control',
                        'tooltip' => 'plugin.citrix.last_name.tooltip',
                    ),
                    'required' => true,
                    'constraints' => [
                        new NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                )
            );
        }

        $builder->add(
            'email',
            'choice',
            array(
                'choices' => $choices,
                'expanded' => false,
                'label_attr' => array('class' => 'control-label'),
                'multiple' => false,
                'label' => 'plugin.citrix.selectidentifier',
                'attr' => array(
                    'class' => 'form-control',
                    'tooltip' => 'plugin.citrix.selectidentifier.tooltip',
                ),
                'required' => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'citrix_submit_action';
    }
}
