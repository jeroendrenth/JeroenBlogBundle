<?php

namespace Jeroen\Bundle\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Jeroen\Bundle\BlogBundle\Entity\Blog;
use Jeroen\Bundle\BlogBundle\Form\Type\BlogType;
use Jeroen\Bundle\BlogBundle\Form\Type\ConfirmType;

use Doctrine\ORM\Mapping as ORM;

class DefaultController extends Controller
{	
    public function indexAction()
    {
      $repository = $this->getDoctrine()->getRepository('JeroenBlogBundle:Blog');
      $blogs = $repository->loadList();

      return $this->render('JeroenBlogBundle:Default:index.html.twig', array('blogs' => $blogs));
    }

    public function viewAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('JeroenBlogBundle:Blog');
        $blog = $repository->find($id);

        if (!$blog) {
            throw $this->createNotFoundException(
                'No blog found for id '.$id
            );
        }

        return $this->render('JeroenBlogBundle:Default:blog.html.twig', array('blog' => $blog));
    }

    public function addAction(Request $request) 
    {
        // Get current user.
        $user = $this->get('security.context')->getToken()->getUser();

    	// create a task and give it some dummy data for this example
        $blog = new Blog;

        // Get blog edit form.
        $form = $this->createForm(new BlogType(), $blog);
        $form->handleRequest($request);

	    if ($form->isValid()) {
                $data = $request->request->get('blog');

	        // perform some action, such as saving the task to the database
                $blog = new Blog();
                $blog->setTitle($data['title']);
                $blog->setBody($data['body']);
                $blog->setCreationDate(new \DateTime());
                $blog->setUid($user->getId());

                $em = $this->getDoctrine()->getManager();
                $em->persist($blog);
                $em->flush();


	        return $this->redirect($this->generateUrl('jeroen_blog_homepage'));
	    }

        return $this->render('JeroenBlogBundle:Default:blogform.html.twig', array(
            'form' => $form->createView()
        ));
    }


    public function editAction(Request $request, $id) 
    {
        // Get current user.
        $user = $this->getUser();

        // create a task and give it some dummy data for this example
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository('JeroenBlogBundle:Blog')->find($id);

        if (!$blog) {
            throw $this->createNotFoundException(
                'No blog found for id '.$id
            );
        }

        // Get blog edit form.
        $form = $this->createForm(new BlogType(), $blog);
        $form->handleRequest($request);

        if ($form->isValid()) {
                $data = $request->request->get('blog');

            // perform some action, such as saving the task to the database
                $blog = new Blog();
                $blog->setTitle($data['title']);
                $blog->setBody($data['body']);
                $blog->setCreationDate(new \DateTime());
                $blog->setUid($user->getId());

                $em->flush();


            return $this->redirect($this->generateUrl('jeroen_blog_homepage'));
        }

        return $this->render('JeroenBlogBundle:Default:blogform.html.twig', array(
            'id' => $id,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction(Request $request, $id) {
        $form = $this->createForm(new ConfirmType());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $blog = $em->getRepository('JeroenBlogBundle:Blog')->find($id);

            $em->remove($blog);
            $em->flush();

            return $this->redirect($this->generateUrl('jeroen_blog_homepage'));
        }
        return $this->render('JeroenBlogBundle:Default:confirmform.html.twig', array(
            'id' => $id,
            'form' => $form->createView(),
        ));

    }
}
