<?php

namespace OHMedia\FileBundle\Controller;

use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Entity\Image;
use OHMedia\FileBundle\Form\Type\FileCreateType;
use OHMedia\FileBundle\Repository\FileFolderRepository;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Security\Voter\FileVoter;
use OHMedia\FileBundle\Service\FileManager;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File as FileConstraint;

abstract class AbstractFileController extends AbstractController
{
    abstract protected function indexRender(array $items, File $newFile, FileFolder $newFileFolder, Image $newImage): Response;

    abstract protected function createRender(FormView $formView, File $file): Response;

    abstract protected function deleteRender(FormView $formView, File $file): Response;

    #[Route('/files', name: 'file_index', methods: ['GET'])]
    public function index(
        FileManager $fileManager,
        FileRepository $fileRepository,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $newFile = (new File())->setBrowser(true);
        $newFolder = (new FileFolder())->setBrowser(true);
        $newImage = (new Image())->setFile($newFile);

        $this->denyAccessUnlessGranted(
            FileVoter::INDEX,
            $newFile,
            'You cannot access the list of files.'
        );

        $items = $fileManager->getListing();

        return $this->indexRender($items, $newFile, $newFolder, $newImage);
    }

    #[Route('/file/create', name: 'file_create_no_folder', methods: ['GET', 'POST'])]
    public function createNoFolder(
        Request $request,
        FileRepository $fileRepository
    ): Response {
        $file = (new File())->setBrowser(true);

        return $this->create($request, $fileRepository, $file);
    }

    #[Route('/folder/{id}/file/create', name: 'file_create_with_folder', methods: ['GET', 'POST'])]
    public function createWithFolder(
        Request $request,
        FileRepository $fileRepository,
        FileFolder $folder
    ): Response {
        $file = (new File())->setBrowser(true);

        $folder->addFile($file);

        return $this->create($request, $fileRepository, $file);
    }

    private function create(
        Request $request,
        FileRepository $fileRepository,
        File $file
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::CREATE,
            $file,
            'You cannot create a new file.'
        );

        $form = $this->createForm(FileCreateType::class, $file, [
            // TODO: what should these be?
            'file_constraints' => [
                new FileConstraint([
                    'mimeTypes' => [
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.oasis.opendocument.presentation',
                        'application/vnd.oasis.opendocument.spreadsheet',
                        'application/vnd.oasis.opendocument.text',
                        'application/vnd.ms-powerpoint',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/rtf',
                        'text/csv',
                        'text/plain',
                        'audio/mpeg',
                        'video/mp4',
                        'video/mpeg',
                    ],
                    'mimeTypesMessage' => 'Only text files, documents, audio, and video are accepted for upload.',
                ]),
            ],
        ]);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileRepository->save($file, true);

            $this->addFlash('notice', 'The file was created successfully.');

            return $this->createRedirect($file);
        }

        return $this->createRender($form->createView(), $file);
    }

    #[Route('/file/{id}/lock', name: 'file_lock', methods: ['POST'])]
    public function lock(
        Request $request,
        File $file,
        FileRepository $fileRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::LOCK,
            $file,
            'You cannot lock this file.'
        );

        $csrfTokenName = 'lock_file'.$file->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $file->setLocked(true);

            $fileRepository->save($file, true);

            $this->addFlash('notice', 'The file was locked successfully.');
        }

        return $this->lockRedirect($file);
    }

    #[Route('/file/{id}/unlock', name: 'file_unlock', methods: ['POST'])]
    public function unlock(
        Request $request,
        File $file,
        FileRepository $fileRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::UNLOCK,
            $file,
            'You cannot unlock this file.'
        );

        $csrfTokenName = 'unlock_file'.$file->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $file->setLocked(false);

            $fileRepository->save($file, true);

            $this->addFlash('notice', 'The file was unlocked successfully.');
        }

        return $this->unlockRedirect($file);
    }

    protected function createRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function lockRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function unlockRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function formRedirect(File $file): Response
    {
        if ($folder = $file->getFolder()) {
            return $this->redirectToRoute('folder_view', [
                'id' => $folder->getId(),
            ]);
        }

        return $this->redirectToRoute('file_index');
    }

    #[Route('/file/{id}/delete', name: 'file_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        File $file,
        FileRepository $fileRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::DELETE,
            $file,
            'You cannot delete this file.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileRepository->remove($file, true);

            $this->addFlash('notice', 'The file was deleted successfully.');

            return $this->deleteRedirect($file);
        }

        return $this->deleteRender($form->createView(), $file);
    }

    protected function deleteRedirect(File $file): Response
    {
        if ($folder = $file->getFolder()) {
            return $this->redirectToRoute('file_folder_view', [
                'id' => $folder->getId(),
            ]);
        }

        return $this->redirectToRoute('file_index');
    }
}
