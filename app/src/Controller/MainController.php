<?php

namespace App\Controller;

use App\Services\JsonService;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MainController extends Controller
{
    /**
     * @Route("/", name="index")
     */
	public function index_page(Request $request, JsonService $json){
        $form = $this->createFormBuilder()
            ->add('name', TextType::class)
            ->add('file', FileType::class)
            ->add('save', SubmitType::class, array('label' => 'upload'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            var_dump($json->is_json($form->getData()['file']->getPathName()));
            var_dump($form->getData()['file']);

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($task);
            // $entityManager->flush();

            //return $this->redirectToRoute('other');
        }

        return $this->render('index.html.twig', array(
            'form' => $form->createView(),
        ));
	}

    /**
     * @Route("/other", name="other")
     */
	public function other_page(){
	    return $this->render('base.html.twig');
    }
}