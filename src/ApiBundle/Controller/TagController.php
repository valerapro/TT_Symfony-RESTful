<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Tags;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class TagController extends FOSRestController implements ClassResourceInterface
{

    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: get all tags",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          409="Un success"
     *       },
     * )
     *
     * @return View
     */
    public function cgetAction()
    {
        $tags = [];
        try {
            $tags = $this->getDoctrine()->getRepository('ApiBundle:Tags')->findAll();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success',
            'tag' => $tags
        ], Response::HTTP_OK);
    }

    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: get tag by id",
     *      resource=true,
     *      statusCodes={
     *           200="Success",
     *           404="Tag not found",
     *           409="Un success"
     *          },
     *      requirements = {
     *      { "name"="id", "dataType"="integer", "requirement"="\d+", "description" = "tagId" }
     *  },
     *     parameters = {
     *      { "name"="id", "dataType"="integer", "required"=true, "description"="tagId" }
     *  }
     * )
     *
     * @param $id int
     * @return View
     */
    public function getAction($id)
    {
        $tag = [];
        $statusCode = Response::HTTP_OK;
        $message = 'Success';
        try {
            $tag = $this->getDoctrine()->getRepository('ApiBundle:Tags')->find($id);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        if (null === $tag) {
            $message = 'Tag id wrong';
            $statusCode = Response::HTTP_NOT_FOUND;
        }

        return $this->view([
            'status' => $statusCode,
            'message' => $message,
            'tag' => $tag
        ], $statusCode);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *     description="Info: add new tag",
     *      resource=true,
     *      statusCodes={
     *          200="Success"
     *      },
     *      requirements = {
     *          {  "name"="title", "dataType"="string",  "description" = "tag title"   }
     *      }
     * )
     *
     * @param Request $request
     * @return View
     */
    public function postAction(Request $request)
    {
        $newTitle = $request->request->get('title');
        try {
            $em = $this->getDoctrine()->getManager();
            $tag = new Tags();
            $tag->setTitle($newTitle);
            $em->persist($tag);
            $em->flush();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success add tag',
            'tag' => $newTitle,
            'id' => $tag->getId()
        ], Response::HTTP_OK);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: delete tag",
     *      resource=true,
     *      statusCodes={
     *          200=" Success",
     *          404=" Tag not found",
     *         },
     *      requirements = {
     *      {  "name"="id", "dataType"="integer", "requirement"="\d+",   "description" = "tag id" }
     *  }
     * )
     *
     * @param $id int
     * @return View
     */
    public function deleteAction($id)
    {
        $tag = $this->getDoctrine()->getRepository('ApiBundle:Tags')->find($id);

        if ($tag === null) {
            $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Tag not found. Wrong id',
            ], Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * Delete all records in PhotoTag
         */
        try {
            $photoTag = $this->getDoctrine()->getRepository('ApiBundle:PhotoTag')->findBy(['tag' => $id ]);
            foreach ($photoTag as $value){
                $em->remove($value);
                $em->flush();
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        /**
         * Delete record in Tag
         */
        try {
            $em->remove($tag);
            $em->flush();

        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success delete tag. Id:' . $id . ' Title:' . $tag->getTitle(),
        ], Response::HTTP_OK);

    }

    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: get all photos by tagId",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          404="Tag not found",
     *        },
     *  requirements = {
     *      {  "name"="tagId",   "dataType"="integer",   "requirement"="\d+",   "description" = "Tag id"   }
     *  }
     * )
     *
     * @param $tagId int
     * @return View
     */
    public function getPhotosAction($tagId)
    {
        $tag = $this->getDoctrine()->getRepository('ApiBundle:Tags')->find($tagId);

        if (null == $tag) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Wrong tag'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success',
            'photos' => $tag->getPhoto()
        ], Response::HTTP_OK);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: edit tag title",
     *      resource=true,
     *      statusCodes=
     *      {
     *          200="Success",
     *          404="Tag not found",
     *      },
     *  requirements = {
     *      { "name"="id", "dataType"="integer", "requirement"="\d+", "description" = "Tag id" },
     *      { "name"="title", "dataType"="string",  "description" = "Tag id" }
     *  }
     * )
     *
     * @param $id int
     * @param $title string
     * @return View
     */
    public function putTitleAction($id, $title)
    {
        $tag = $this->getDoctrine()->getRepository('ApiBundle:Tags')->find($id);
        if ($tag === null) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Wrong tagId',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $em = $this->getDoctrine()->getManager();
            $tag->setTitle($title);
            $em->persist($tag);
            $em->flush();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success',
            'title' => $title,
        ], Response::HTTP_OK);
    }

}
