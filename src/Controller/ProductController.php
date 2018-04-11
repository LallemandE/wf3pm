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
use App\Entity\Comment;
use App\Entity\CommentFile;

use Symfony\Component\Form\FormFactory;
use App\Form\CommentType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Ramsey\Uuid\Uuid;


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
                FormFactoryInterface $formFactory,
                SessionInterface $session,
                TokenStorageInterface $tokenStorage)
    {
        $id = $request->query->get('id');
        if ($id){
            $product = $productRepository->findOneById($id);
            
            if ($product){
                
                $comment = new Comment();
                $form = $formFactory->create(
                    CommentType::class,
                    $comment,
                    ['stateless' => true]);
                
                // pour le traitement du formulaire d'ajout d'un commentaire
                
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()){
                    
                    // save the comment
                    
                    // convert FileDto to File (name, url, mimetype) ... et on veut stocker le fichier dans \upload
                    // je crée un tableau vide dans lequel je vais mettre mes file comment dans le bon format (et pas en FileDto)
                    $tmpCommentFile = [];
                    
                    foreach ($comment->getFiles() as $fileArray) {
                        foreach ($fileArray as $file) {
                            $name = sprintf(
                                '%s.%s',
                                Uuid::uuid1(),
                                $file->getClientOriginalExtension()
                                );
                            
                            $commentFile = new CommentFile();
                            $commentFile->setComment($comment)
                            ->setMimeType($file->getMimeType())
                            ->setName($file->getClientOriginalName())
                            ->setFileUrl('/upload/'.$name);
                            
                            $tmpCommentFile[] = $commentFile;
                            
                            $file->move(
                                __DIR__.'/../../public/upload',
                                $name
                                );
                        }
                    }
                    
                    $token = $tokenStorage->getToken();
                    if (!$token){
                        throw new \Exception();
                    }
                    $user = $token->getUser();
                    if (!$user){
                        throw new \Exception();
                    }
                    /*
                    var_dump($token);
                    echo '<br> !!! USER !!! <br>';
                    var_dump($user);
                    */
                    $comment->setFiles($tmpCommentFile)
                    ->setAuthor($user)
                    ->setProduct($product);

                // A ce niveau, on n'a pas encore faire la mise en pase de données.
                
                    
                    
                    
                }
                
                return new Response($twig->render('Product/displayProduct.html.twig', ['product' => $product, 'form' => $form->createView()]));
            }
            
            $session->getFlashBag()->add("info", "Product not found");
            return new RedirectResponse($urlGenerator->generate('homepage'));
            
        }
        
        $session->getFlashBag()->add("info", "Product key required");
        return new RedirectResponse($urlGenerator->generate('homepage'));
        
    } // singleDisplay Method
}

