<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21.03.19
 * Time: 23:29
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FeedbackForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);
        $builder->add('text', TextareaType::class, ['required' => true]);
        $builder->add('submit', SubmitType::class);
        return $builder->getForm();
    }
}