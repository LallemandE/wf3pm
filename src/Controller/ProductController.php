<?php
namespace App\Controller;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 *
 * @author Etudiant
 *        
 */
class ProductController
{
    public function addProduct (
        Environment $twig,
        FormFactoryInterface $factory,
        Request $request,
        ObjectManager $manager,
        SessionInterface $session)
    {
        $product = new Product();
        
        $builder = $factory->createBuilder(FormType::class, $product);
        $builder->add('name', TextType::class, 
                        ['attr' => ['placeholder' => 'Enter Product name here !']])
                ->add('description', TextareaType::class,
                    [   'required' => false,
                        'attr' => ['placeholder' => 'Enter Product description here (not compulsory) !',
                                'rows' => 6,
                                'cols' => 33]])
                ->add('version', TextType::class,
                    ['attr' => ['placeholder' => 'Enter version here !']])
                ->add('submit', SubmitType::class);
        
        $form = $builder->getForm();
        
        // Gestion du formulaire sur un post
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->persist($product);
            $manager->flush();
            
            $session->getFlashBag()->add("info", "your product was created");
            
            return new RedirectResponse('/');
            
        }
        
        return new Response($twig->render ('Product/addProduct.html.twig', 
                            ['formular' => $form->createView()]));
    }
}

