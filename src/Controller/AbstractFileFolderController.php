<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Form\Type\FileFolderCreateType;
use OHMedia\FileBundle\Form\Type\FileFolderEditType;
use OHMedia\FileBundle\Repository\FileFolderRepository;
use OHMedia\FileBundle\Security\Voter\FileFolderVoter;
use OHMedia\FileBundle\Service\FileListing;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractFileFolderController extends AbstractController
{
    abstract protected function viewRender(FileFolder $folder, array $items, File $newFile, FileFolder $newFileFolder, Image $newImage): Response;

    abstract protected function createRender(FormView $formView, FileFolder $folder): Response;

    abstract protected function editRender(FormView $formView, FileFolder $folder): Response;

    abstract protected function deleteRender(FormView $formView, FileFolder $folder): Response;

    #[Route('/folder/create', name: 'file_folder_create_no_folder', methods: ['GET', 'POST'])]
    public function createNoFolder(
        Request $request,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $folder = (new FileFolder())->setBrowser(true);

        return $this->create($request, $fileFolderRepository, $folder);
    }

    #[Route('/folder/{id}/folder/create', name: 'file_folder_create_with_folder', methods: ['GET', 'POST'])]
    public function createWithFolder(
        Request $request,
        FileFolderRepository $fileFolderRepository,
        FileFolder $parent
    ): Response {
        $folder = (new FileFolder())->setBrowser(true);

        $parent->addFolder($folder);

        return $this->create($request, $fileFolderRepository, $folder);
    }

    private function create(
        Request $request,
        FileFolderRepository $fileFolderRepository,
        FileFolder $folder
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::CREATE,
            $folder,
            'You cannot create a new folder.'
        );

        $form = $this->createForm(FileFolderCreateType::class, $folder);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileFolderRepository->save($folder, true);

            $this->addFlash('notice', 'The folder was created successfully.');

            return $this->createRedirect($folder);
        }

        return $this->createRender($form->createView(), $folder);
    }

    #[Route('/folder/{id}', name: 'file_folder_view', methods: ['GET'])]
    public function view(FileFolder $folder, FileListing $fileListing): Response
    {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::VIEW,
            $folder,
            'You cannot view this folder.'
        );

        $newFile = (new File())
            ->setBrowser(true)
            ->setFolder($folder);

        $newFolder = (new FileFolder())
            ->setBrowser(true)
            ->setFolder($folder);

        $newImage = (new Image())
            ->setFile($newFile);

        $items = $fileListing->get($folder);

        return $this->viewRender($folder, $items, $newFile, $newFolder, $newImage);
    }

    #[Route('/folder/{id}/edit', name: 'file_folder_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        FileFolder $folder,
        FileFolderRepository $folderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::EDIT,
            $folder,
            'You cannot edit this folder.'
        );

        $form = $this->createForm(FileFolderEditType::class, $folder);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $folderRepository->save($folder, true);

            $this->addFlash('notice', 'Changes to the folder were saved successfully.');

            return $this->editRedirect($folder);
        }

        return $this->editRender($form->createView(), $folder);
    }

    #[Route('/folder/{id}/lock', name: 'file_folder_lock', methods: ['POST'])]
    public function lock(
        Request $request,
        FileFolder $folder,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::LOCK,
            $folder,
            'You cannot lock this folder.'
        );

        $csrfTokenName = 'lock_file_folder_'.$folder->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $folder->setLocked(true);

            $fileFolderRepository->save($folder, true);

            $this->addFlash('notice', 'The folder was locked successfully.');
        }

        return $this->lockRedirect($folder);
    }

    #[Route('/folder/{id}/unlock', name: 'file_folder_unlock', methods: ['POST'])]
    public function unlock(
        Request $request,
        FileFolder $folder,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::UNLOCK,
            $folder,
            'You cannot unlock this folder.'
        );

        $csrfTokenName = 'unlock_file_folder_'.$folder->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $folder->setLocked(false);

            $fileFolderRepository->save($folder, true);

            $this->addFlash('notice', 'The folder was unlocked successfully.');
        }

        return $this->unlockRedirect($folder);
    }

    protected function createRedirect(FileFolder $folder): Response
    {
        return $this->formRedirect($folder);
    }

    protected function editRedirect(FileFolder $folder): Response
    {
        return $this->formRedirect($folder);
    }

    protected function lockRedirect(FileFolder $folder): Response
    {
        return $this->formRedirect($folder);
    }

    protected function unlockRedirect(FileFolder $folder): Response
    {
        return $this->formRedirect($folder);
    }

    protected function formRedirect(FileFolder $folder): Response
    {
        return $this->redirectToRoute('file_folder_view', [
            'id' => $folder->getId(),
        ]);
    }

    #[Route('/folder/{id}/delete', name: 'file_folder_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        FileFolder $folder,
        FileFolderRepository $folderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::DELETE,
            $folder,
            'You cannot delete this folder.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $folderRepository->remove($folder, true);

            $this->addFlash('notice', 'The folder was deleted successfully.');

            return $this->deleteRedirect($folder);
        }

        return $this->deleteRender($form->createView(), $folder);
    }

    protected function deleteRedirect(FileFolder $folder): Response
    {
        if ($parent = $folder->getFolder()) {
            return $this->redirectToRoute('file_folder_view', [
                'id' => $parent->getId(),
            ]);
        }

        return $this->redirectToRoute('file_folder_index');
    }
}
