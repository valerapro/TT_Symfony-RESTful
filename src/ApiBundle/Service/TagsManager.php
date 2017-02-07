<?php

namespace ApiBundle\Service;

use ApiBundle\Entity\Photos;
use ApiBundle\Entity\Tags;
use ApiBundle\Entity\PhotoTag;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class TagsManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Add tags to photo
     *
     * @param $tagsId
     * @param Photos $photo
     * @return PhotoTag
     */
    public function addTags($tagsId, Photos $photo)
    {
        $tagsIdArr = explode(',', $tagsId);
        foreach ($tagsIdArr as $value) {
            $tag = trim($value);

            $existTag = $this->findTag($tag);
            $photoTagExist = $this->findPhotoTag($tag, $photo);

            if (null === $existTag) {
                continue;
            }
            if (null != $photoTagExist) {
                continue;
            }
                $photoTag = new PhotoTag();
                $photoTag->setTag($existTag);
                $photoTag->setPhoto($photo);
                $this->em->persist($photoTag);
                $this->em->flush();
        }
        return $photoTag;
    }

    /**
     * Find record in photoTag by photoId and tagId
     *
     * @param $photo
     * @param $tag
     * @return null|object
     */
    public function findPhotoTag($tag, $photo)
    {
        $photoTagRepository = $this->em->getRepository('ApiBundle:PhotoTag');
        $photoTagExist = $photoTagRepository->findOneBy([
            'photo' => $photo,
            'tag' => $tag,
        ]);
        return $photoTagExist;
    }

    /**
     * Find record in photoTag by photoId
     *
     * @param $photo
     * @return null|object
     */
    public function findPhotoTagByPhoto($photo)
    {
        $photoTagRepository = $this->em->getRepository('ApiBundle:PhotoTag');
        $photoTagExist = $photoTagRepository->findBy([
            'photo' => $photo
        ]);
        return $photoTagExist;
    }

    /**
     * Check for existing tag in DB
     *
     * @param $tag
     * @return null|object
     */
    public function findTag($tag)
    {
        $tagRepository = $this->em->getRepository('ApiBundle:Tags');
        $existTag = $tagRepository->find($tag);

        return $existTag;
    }
}
