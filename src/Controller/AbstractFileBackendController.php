<?php

namespace OHMedia\FileBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Form\FileCreateType;
use OHMedia\FileBundle\Form\FileEditType;
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

abstract class AbstractFileBackendController extends AbstractController
{
    abstract protected function indexRender(QueryBuilder $queryBuilder): Response;

    abstract protected function createRender(FormView $formView, File $file): Response;

    abstract protected function editRender(FormView $formView, File $file): Response;

    abstract protected function deleteRender(FormView $formView, File $file): Response;

    #[Route('/files', name: 'file_index', methods: ['GET'])]
    public function index(
        FileManager $fileManager,
        FileRepository $fileRepository,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $newFile = (new File())->setBrowser(true);
        $newFolder = (new FileFolder())->setBrowser(true);

        $this->denyAccessUnlessGranted(
            FileVoter::INDEX,
            $newFile,
            'You cannot access the list of files.'
        );

        $items = $fileManager->getListing();

        return $this->indexRender($items, $newFile, $newFolder);
    }

    // folder/create
    // folder/{id}/folder/create

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

        $form = $this->createForm(FileCreateType::class, $file);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileRepository->save($file, true);

            $this->addFlash('notice', 'The file was created successfully.');

            return $this->createRedirect($file);
        }

        return $this->createRender($form->createView(), $file);
    }

    #[Route('/file/{id}/edit', name: 'file_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        File $file,
        FileRepository $fileRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::EDIT,
            $file,
            'You cannot edit this file.'
        );

        $form = $this->createForm(FileEditType::class, $file);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileRepository->save($file, true);

            $this->addFlash('notice', 'Changes to the file were saved successfully.');

            return $this->editRedirect($file);
        }

        return $this->editRender($form->createView(), $file);
    }

    protected function createRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function editRedirect(File $file): Response
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
