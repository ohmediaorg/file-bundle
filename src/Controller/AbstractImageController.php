<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Form\Type\ImageCreateType;
use OHMedia\FileBundle\Form\Type\ImageEditType;
use OHMedia\FileBundle\Repository\ImageRepository;
use OHMedia\FileBundle\Security\Voter\ImageVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractImageController extends AbstractController
{
    abstract protected function createRender(FormView $formView, Image $image): Response;

    abstract protected function editRender(FormView $formView, Image $image): Response;

    #[Route('/image/create', name: 'image_create_no_folder', methods: ['GET', 'POST'])]
    public function createNoFolder(
        Request $request,
        ImageRepository $imageRepository
    ): Response {
        $file = (new File())->setBrowser(true);
        $image = (new Image())->setFile($file);
        $file->setImage($image);

        return $this->create($request, $imageRepository, $image);
    }

    #[Route('/folder/{id}/image/create', name: 'image_create_with_folder', methods: ['GET', 'POST'])]
    public function createWithFolder(
        Request $request,
        ImageRepository $imageRepository,
        FileFolder $folder
    ): Response {
        $file = (new File())->setBrowser(true);
        $image = (new Image())->setFile($file);
        $file->setImage($image);

        $folder->addFile($file);

        return $this->create($request, $imageRepository, $image);
    }

    private function create(
        Request $request,
        ImageRepository $imageRepository,
        Image $image
    ): Response {
        $this->denyAccessUnlessGranted(
            ImageVoter::CREATE,
            $image,
            'You cannot create a new image.'
        );

        $form = $this->createForm(ImageCreateType::class, $image);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $locked = $form->get('locked')->getData();

            $image->getFile()->setLocked($locked);

            $imageRepository->save($image, true);

            $this->addFlash('notice', 'The image was created successfully.');

            return $this->createRedirect($image);
        }

        return $this->createRender($form->createView(), $image);
    }

    #[Route('/image/{id}/edit', name: 'image_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Image $image,
        ImageRepository $imageRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            ImageVoter::EDIT,
            $image,
            'You cannot edit this image.'
        );

        $form = $this->createForm(ImageEditType::class, $image);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageRepository->save($image, true);

            $this->addFlash('notice', 'Changes to the image were saved successfully.');

            return $this->editRedirect($image);
        }

        return $this->editRender($form->createView(), $image);
    }

    protected function createRedirect(Image $image): Response
    {
        return $this->formRedirect($image);
    }

    protected function editRedirect(Image $image): Response
    {
        return $this->formRedirect($image);
    }

    protected function formRedirect(Image $image): Response
    {
        if ($folder = $image->getFile()->getFolder()) {
            return $this->redirectToRoute('file_folder_view', [
                'id' => $folder->getId(),
            ]);
        }

        return $this->redirectToRoute('file_index');
    }
}
