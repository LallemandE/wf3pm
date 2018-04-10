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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\ProductRepository;


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
        UrlGeneratorInterface $urlGenerator,
        SessionInterface $session)
    {
        $product = new Product();
        
        $builder = $factory->createBuilder(FormType::class, $product);
        $builder->add('name', TextType::class, 
                        ['label' => 'FORM.PRODUCT.NAME.ITEM', 'attr' => ['placeholder' => 'FORM.PRODUCT.NAME.PLACEHOLDER']])
                ->add('description', TextareaType::class,
                    ['label' => 'FORM.PRODUCT.DESCRIPTION.ITEM',
                     'required' => false,
                     'attr' => ['placeholder' => 'FORM.PRODUCT.DESCRIPTION.PLACEHOLDER',
                                'rows' => 6,
                                'cols' => 90]])
                 ->add('version', TextType::class,['label' => 'FORM.PRODUCT.VERSION.ITEM',
                'attr' => ['placeholder' => 'FORM.PRODUCT.VERSION.PLACEHOLDER']])
                    ->add('submit', SubmitType::class, ['label' => 'FORM.SUBMIT']);
        
        $form = $builder->getForm();
        
        // Gestion du formulaire sur un post
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->persist($product);
            $manager->flush();
            
            $session->getFlashBag()->add("info", "your product was created");
            
            return new RedirectResponse($urlGenerator->generate('homepage'));
            
        }
        
        return new Response($twig->render ('Product/addProduct.html.twig', 
                            ['formular' => $form->createView()]));
    }
    
    public function list(
                Environment $twig,
                FormFactoryInterface $factory,
                ObjectManager $manager,
                UrlGeneratorInterface $urlGenerator,
                SessionInterface $session
               
        ) 
    {
        
        $productRepository = $manager->getRepository(Product::class);
        $productArray = $productRepository->findAll();
        
        if ($productArray){
            return new Response($twig->render('Product/listProduct.html.twig', ['productArray' => $productArray]));
        }
        $session->getFlashBag()->add("info", "No product registered");
        return new RedirectResponse($urlGenerator->generate('homepage'));
        
    }
    
    public function singleDisplay(
                Environment $twig,
                Request $request,
                ProductRepository $productRepository,
                UrlGeneratorInterface $urlGenerator,
                SessionInterface $session)
    {
        $id = $request->query->get('id');
        if ($id){
            $product = $productRepository->findOneById($id);
            
            if ($product){
                return new Response($twig->render('Product/displayProduct.html.twig', ['product' => $product]));
            }
            
            $session->getFlashBag()->add("info", "Product not found");
            return new RedirectResponse($urlGenerator->generate('homepage'));
            
        }
        
        $session->getFlashBag()->add("info", "Product key required");
        return new RedirectResponse($urlGenerator->generate('homepage'));
        
    } // singleDisplay Method
}

