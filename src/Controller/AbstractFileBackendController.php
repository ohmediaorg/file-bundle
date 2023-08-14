<?php

namespace OHMedia\FileBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Form\FileCreateType;
use OHMedia\FileBundle\Form\FileEditType;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Security\Voter\FileVoter;
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

    abstract protected function copyRender(FormView $formView, File $file): Response;

    abstract protected function deleteRender(FormView $formView, File $file): Response;

    #[Route('/files', name: 'file_index', methods: ['GET'])]
    public function index(
        FileRepository $fileRepository,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::INDEX,
            new File(),
            'You cannot access the list of files.'
        );

        $fileQueryBuilder = $fileRepository->createQueryBuilder('f');
        $fileQueryBuilder->where('f.browser = 1');

        $fileFolderQueryBuilder = $fileFolderRepository->createQueryBuilder('f');
        $fileFolderQueryBuilder->where('f.browser = 1');

        return $this->indexRender($fileQueryBuilder, $fileFolderQueryBuilder);
    }

    // folder/create
    // folder/{id}/folder/create

    #[Route('/file/create', name: 'file_create_no_folder', methods: ['GET', 'POST'])]
    public function createFileNoFolder(
        Request $request,
        FileRepository $fileRepository
    ): Response {
        $file = new File();
        $file->setBrowser(true);

        return $this->createFile($request, $fileRepository, $file);
    }

    #[Route('/folder/{id}/file/create', name: 'file_create_with_folder', methods: ['GET', 'POST'])]
    public function createFileInFolder(
        Request $request,
        FileRepository $fileRepository,
        FileFolder $folder
    ): Response {
        $file = new File();
        $file->setBrowser(true);

        $folder->addFile($file);

        return $this->createFile($request, $fileRepository, $file);
    }

    private function createFile(
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

        $this->setFileSlug($file, $fileRepository);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileRepository->save($file, true);

            $this->addFlash('notice', 'Changes to the file were saved successfully.');

            return $this->editRedirect($file);
        }

        return $this->editRender($form->createView(), $file);
    }

    #[Route('/file/{id}/copy', name: 'file_copy', methods: ['GET', 'POST'])]
    public function copy(
        Request $request,
        File $file,
        FileRepository $fileRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::DUPLICATE,
            $file,
            'You cannot copy this file.'
        );

        $file = clone $file;

        $form = $this->createForm(FileCreateType::class, $file);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        $this->setFileSlug($file, $fileRepository);

        if ($form->isSubmitted() && $form->isValid()) {
            // make sure this file is last locally
            // the event subscriber will clean things up
            $file->setOrderLocal(File::ORDER_LOCAL_END);

            $fileRepository->save($file, true);

            $this->addFlash('notice', 'The file was copyd successfully.');

            return $this->copyRedirect($file);
        }

        return $this->copyRender($form->createView(), $file);
    }

    protected function createRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function editRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function copyRedirect(File $file): Response
    {
        return $this->formRedirect($file);
    }

    protected function formRedirect(File $file): Response
    {
        // TODO: redirect to either file index or folder index
        return $this->redirectToRoute('file_view', [
            'id' => $file->getId(),
        ]);
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

        $this->addFlash('warning', 'Deleting a file will also delete all descendant files!');

        return $this->deleteRender($form->createView(), $file);
    }

    protected function deleteRedirect(File $file): Response
    {
        return $this->redirectToRoute('file_index');
    }
}
