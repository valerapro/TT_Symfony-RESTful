<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Photos;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class PhotoController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: get photo by id",
     *      resource=true,
     *      statusCodes={
     *          200="Successful",
     *          404="Photo not found",
     *      },
     *      requirements = {
     *          { "name"="id", "dataType"="integer",  "requirement"="\d+", "description" = "Photo id" }
     *      }
     * )
     *
     * @param $id int
     * @return View
     */
    public function getAction($id)
    {
        $photo = [];
        $statusCode = Response::HTTP_OK;
        $message = 'Success';
        try {
            $photo = $this->getDoctrine()->getRepository('ApiBundle:Photos')->find($id);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        if (null === $photo) {
            $message = 'id wrong';
            $statusCode = Response::HTTP_NOT_FOUND;
        }

        return $this->view([
            'status' => $statusCode,
            'message' => $message,
            'upload_file_dir' => $this->getParameter('upload_file_dir'),
            'photo' => $photo,
        ], $statusCode);

    }

    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @Rest\FileParam(
     *   name="file",
     *   key=null,
     *   requirements={},
     *   default=null,
     *   description="",
     *   strict=true,
     *   nullable=false,
     *   image=true
     * )
     * @ApiDoc(
     *      description="Info: add new photo. In optional tags id for new photo",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          409="fail",
     *  },
     *  requirements = {
     *      {"name"="tags", "dataType"="string", "description" = "Tags id separated by comma"},
     *      {"name"="file", "dataType"="file", "description" = "Image file: jpg, png"}
     *  }
     * )
     *
     * @param Request $request
     * @return View
     */
    public function postAction(Request $request)
    {
        $file = $request->files->get('file');
        $em = $this->getDoctrine()->getManager();
        $fileValidate = $this->get('files_manager')->fileValidate($file);

        $statusCode = Response::HTTP_CONFLICT;
        $message = 'Image mast be: ' . $this->get('files_manager')->getALLOWEDMIMETYPES();
        $photo = [];
        if (null !== $file && $fileValidate) {
            $statusCode = Response::HTTP_OK;
            $message = 'Photo successfully loaded on server';

            try {
                $photo = new Photos();
                $fileName = $this->get('files_manager')->uploadFile($file);
                $photo->setImage($fileName);
                $em->persist($photo);
                $em->flush();
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }

            try {
                $tagsId = $request->request->get('tags');
                if (null !== $tagsId) {
                    $this->get('tags_manager')->addTags($tagsId, $photo);
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
        return $this->view([
            'status' => $statusCode,
            'message' => $message,
            'photo' => $photo,
        ], $statusCode);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *     description="Info: add tags for photo",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          404="Photo is not found",
     *      },
     *  requirements = {
     *      {"name"="photoId", "dataType"="integer", "description" = "Photo id"},
     *      {"name"="tags", "dataType"="string", "description" = "Tags id separated by comma"},
     *  }
     * )
     *
     * @param Request $request
     * @param $photoId int
     * @return View
     */
    public function postAddtagsAction(Request $request, $photoId)
    {
        $em = $this->getDoctrine()->getManager();

        try {
            $photo = $this->getDoctrine()->getRepository('ApiBundle:Photos')->find($photoId);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        if (null == $photo) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Photo not found. Wrong photoId.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $tags = $request->request->get('tags');
            $this->get('tags_manager')->addTags($tags, $photo);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success',
            'photos' => $photo,
        ], Response::HTTP_OK);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: delete tag from photo",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          404="Photo, tag is not found"
     *         },
     *      requirements = {
     *          {"name"="photoId", "dataType"="integer", "requirement"="\d+", "description" = "Photo id"},
     *          {"name"="tagId", "dataType"="integer", "requirement"="\d+", "description" = "Tag id"},
     *      }
     * )
     *
     * @param $photoId int
     * @param $tagId int
     * @return View
     */
    public function deleteTagAction($photoId, $tagId)
    {
        /**
         * Search items
         */
        $photo = $this->getDoctrine()->getRepository('ApiBundle:Photos')->find($photoId);
        if (null == $photo) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Wrong PhotoId'
            ], Response::HTTP_NOT_FOUND);
        }
        $tag = $this->getDoctrine()->getRepository('ApiBundle:Tags')->find($tagId);
        if (null == $tag) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Wrong TagId'
            ], Response::HTTP_NOT_FOUND);
        }
        /**
         * Search and delete joint record
         */
        $photoTagExist = $this->get('tags_manager')->findPhotoTag($tag, $photo);
        if (null != $photoTagExist) {
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($photoTagExist);
                $em->flush();

            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
            return $this->view([
                'status' => Response::HTTP_OK,
                'message' => 'Success',
                'photos' => $photo
            ], Response::HTTP_OK);
        }
        return $this->view([
            'status' => Response::HTTP_NOT_FOUND,
            'message' => 'The tag is not attached to a photo',
        ], Response::HTTP_NOT_FOUND);

    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: get list all photo with no pagination",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          409="Un success"
     *      },
     * )
     *
     * @return View
     */
    public function cgetAction()
    {
        $photos = [];
        try {
            $photos = $this->getDoctrine()->getRepository('ApiBundle:Photos')->findAll();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success',
            'photos' => $photos
        ], Response::HTTP_OK);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @Rest\Get("/photos/paginated/{limit}/{page}")
     * @ApiDoc(
     *      description="Info: get all photo with pagination",
     *      resource=true,
     *      statusCodes={
     *          200="Duccess",
     *          404="Page not found",
     *      },
     *  requirements = {
     *      {"name"="limit", "dataType"="integer", "requirement"="\d+", "description" = "Limit of photos per page"},
     *      {"name"="page", "dataType"="integer", "requirement"="\d+", "description" = "Page number"},
     *  }
     * )
     */
    public function cgetPaginatedAction($limit, $page)
    {
        try {
            $repository = $this->getDoctrine()->getRepository('ApiBundle:Photos');
            $paginator = $repository->getPaginatedPhotos();
            $totalPages = ceil($paginator->count() / $limit);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        if ($page > $totalPages) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Last page is ' . $totalPages
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $photos = $paginator->getQuery()
                ->setFirstResult($limit * ($page - 1))
                ->setMaxResults($limit)
                ->getResult();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success',
            'totalPages' => $totalPages,
            'photos' => $photos
        ], Response::HTTP_OK);
    }


    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ApiDoc(
     *      description="Info: delete photo",
     *      resource=true,
     *      statusCodes={
     *          200="Success",
     *          404="Photo not found",
     *       },
     *  requirements = {
     *      {"name"="photoId", "dataType"="integer", "requirement"="\d+", "description" = "PhotoId"}
     *  }
     * )
     *
     * @param $photoId
     * @return View
     */
    public function deleteAction($photoId)
    {
        try {
            $photo = $this->getDoctrine()->getRepository('ApiBundle:Photos')->find($photoId);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        if (null == $photo) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Wrong id'
            ], Response::HTTP_NOT_FOUND);
        }
        /**
         * Delete file from server
         */
        $fileName = $photo->getImage();
        $removeFile = $this->get('files_manager')->removeFile($fileName);
        if (!$removeFile) {
            return $this->view([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Error! Delete file in server impossible.',
                'fileName' => $fileName
            ], Response::HTTP_NOT_FOUND);
        }
        /**
         * Delete PhotoTag and photo from DB
         */
        try {
            $em = $this->getDoctrine()->getManager();
            $photoTagExist = $this->get('tags_manager')->findPhotoTagByPhoto($photo);
            foreach ($photoTagExist as $value){
                $em->remove($value);
                $em->flush();
            }
            $em->remove($photo);
            $em->flush();

        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $this->view([
            'status' => Response::HTTP_OK,
            'message' => 'Success'
        ], Response::HTTP_OK);
    }

}
