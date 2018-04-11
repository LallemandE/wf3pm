<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\Comment;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\CommentFileType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 *
 * @author Etudiant
 *        
 */
class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // TODO Auto-generated method stub
        $builder->add('comment', TextareaType::class)
            ->add(
                'files',
                CollectionType::class,
                [
                    'entry_type' => CommentFileType::class,
                    'allow_add' => true
                ]);
            if ($options['stateless']){
                $builder->add('submit', SubmitType::class);
            }
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // TODO Auto-generated method stub
        $resolver->setDefault('data_type', Comment::class);
        $resolver->setDefault('stateless', false);
    }

}

