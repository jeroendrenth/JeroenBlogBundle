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
        $user = $this->container->get('security.context')->getToken()->getUser();
        $logged_in = is_object($user) ? TRUE : FALSE;

    	$repository = $this->getDoctrine()->getRepository('JeroenBlogBundle:Blog');
        $query = $repository->createQueryBuilder('b')
            ->select(array('b.id', 'b.title', 'b.body', 'b.creationDate'))
            ->orderBy('b.creationDate', 'DESC')
            ->getQuery();
        $blogs = $query->getResult();

        $variables = array(
            'blogs' => $blogs, 
            'user' => $user,
            'logged_in' => $logged_in,
        );

        return $this->render('JeroenBlogBundle:Default:index.html.twig', $variables);
    }

    public function viewAction($id)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $blog = $this->getDoctrine()
            ->getRepository('JeroenBlogBundle:Blog')
            ->find($id);

        if (!$blog) {
            throw $this->createNotFoundException(
                'No blog found for id '.$id
            );
        }

        $values = array(
            'blog' => $blog,
            'user' => $user,
        );

        return $this->render('JeroenBlogBundle:Default:blog.html.twig', $values);
    }

    public function addAction(Request $request) 
    {
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

    private function text_summary($text, $format = NULL, $size = NULL) {
      if (!isset($size)) {
        // What used to be called 'teaser' is now called 'summary', but
        // the variable 'teaser_length' is preserved for backwards compatibility.
        $size = 600;
      }

      // Find where the delimiter is in the body
      $delimiter = strpos($text, '<!--break-->');

      // If the size is zero, and there is no delimiter, the entire body is the summary.
      if ($size == 0 && $delimiter === FALSE) {
        return $text;
      }

      // If a valid delimiter has been specified, use it to chop off the summary.
      if ($delimiter !== FALSE) {
        return substr($text, 0, $delimiter);
      }

      // We check for the presence of the PHP evaluator filter in the current
      // format. If the body contains PHP code, we do not split it up to prevent
      // parse errors.
      if (isset($format)) {
        $filters = filter_list_format($format);
        if (isset($filters['php_code']) && $filters['php_code']->status && strpos($text, '<?') !== FALSE) {
          return $text;
        }
      }

      // If we have a short body, the entire body is the summary.
      if (strlen($text) <= $size) {
        return $text;
      }

      // If the delimiter has not been specified, try to split at paragraph or
      // sentence boundaries.

      // The summary may not be longer than maximum length specified. Initial slice.
      $summary = truncate_utf8($text, $size);

      // Store the actual length of the UTF8 string -- which might not be the same
      // as $size.
      $max_rpos = strlen($summary);

      // How much to cut off the end of the summary so that it doesn't end in the
      // middle of a paragraph, sentence, or word.
      // Initialize it to maximum in order to find the minimum.
      $min_rpos = $max_rpos;

      // Store the reverse of the summary. We use strpos on the reversed needle and
      // haystack for speed and convenience.
      $reversed = strrev($summary);

      // Build an array of arrays of break points grouped by preference.
      $break_points = array();

      // A paragraph near the end of sliced summary is most preferable.
      $break_points[] = array('</p>' => 0);

      // If no complete paragraph then treat line breaks as paragraphs.
      $line_breaks = array(
        '<br />' => 6,
        '<br>' => 4,
      );
      // Newline only indicates a line break if line break converter
      // filter is present.
      if (isset($filters['filter_autop'])) {
        $line_breaks["\n"] = 1;
      }
      $break_points[] = $line_breaks;

      // If the first paragraph is too long, split at the end of a sentence.
      $break_points[] = array(
        '. ' => 1,
        '! ' => 1,
        '? ' => 1,
        '。' => 0,
        '؟ ' => 1,
      );

      // Iterate over the groups of break points until a break point is found.
      foreach ($break_points as $points) {
        // Look for each break point, starting at the end of the summary.
        foreach ($points as $point => $offset) {
          // The summary is already reversed, but the break point isn't.
          $rpos = strpos($reversed, strrev($point));
          if ($rpos !== FALSE) {
            $min_rpos = min($rpos + $offset, $min_rpos);
          }
        }

        // If a break point was found in this group, slice and stop searching.
        if ($min_rpos !== $max_rpos) {
          // Don't slice with length 0. Length must be <0 to slice from RHS.
          $summary = ($min_rpos === 0) ? $summary : substr($summary, 0, 0 - $min_rpos);
          break;
        }
      }

      return $summary;
    }

}
