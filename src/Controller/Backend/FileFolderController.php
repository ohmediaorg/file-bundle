<?php

namespace OHMedia\FileBundle\Controller\Backend;

use OHMedia\BootstrapBundle\Component\Breadcrumb;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Form\Type\FileFolderCreateType;
use OHMedia\FileBundle\Form\Type\FileFolderEditType;
use OHMedia\FileBundle\Form\Type\FileFolderMoveType;
use OHMedia\FileBundle\Repository\FileFolderRepository;
use OHMedia\FileBundle\Security\Voter\FileFolderVoter;
use OHMedia\FileBundle\Service\FileListing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FileFolderController extends AbstractController
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

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

            return $this->formRedirect($folder);
        }

        $breadcrumbs = $this->getBreadcrumbs($folder);

        $breadcrumbs[] = new Breadcrumb('Create', '');

        return $this->render('@OHMediaFile/file_folder/file_folder_form.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
            'form_title' => 'Create Folder',
            'breadcrumbs' => $breadcrumbs,
        ]);
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

        $items = $fileListing->get($folder);

        return $this->render('@OHMediaFile/file_folder/file_folder_view.html.twig', [
            'breadcrumbs' => $this->getBreadcrumbs($folder),
            'folder' => $folder,
            'items' => $items,
            'new_file' => $newFile,
            'new_folder' => $newFolder,
        ]);
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

            return $this->formRedirect($folder);
        }

        $breadcrumbs = $this->getBreadcrumbs($folder);

        $breadcrumbs[] = new Breadcrumb('Edit', '');

        return $this->render('@OHMediaFile/file_folder/file_folder_form.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
            'form_title' => 'Edit Folder',
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/folder/{id}/move', name: 'file_folder_move', methods: ['GET', 'POST'])]
    public function move(
        Request $request,
        FileFolder $folder,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::MOVE,
            $folder,
            'You cannot move this folder.'
        );

        $form = $this->createForm(FileFolderMoveType::class, $folder);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileFolderRepository->save($folder, true);

            $this->addFlash('notice', 'The folder was moved successfully.');

            return $this->formRedirect($folder);
        }

        $breadcrumbs = $this->getBreadcrumbs($folder);

        $breadcrumbs[] = new Breadcrumb('Move', '');

        return $this->render('@OHMediaFile/file_folder/file_folder_form.html.twig', [
            'form' => $form->createView(),
            'folder' => $folder,
            'form_title' => 'Move Folder',
            'breadcrumbs' => $breadcrumbs,
        ]);
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

        return $this->formRedirect($folder);
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

        return $this->formRedirect($folder);
    }

    protected function formRedirect(FileFolder $folder): Response
    {
        return $this->redirectToRoute('file_folder_view', [
            'id' => $folder->getId(),
        ]);
    }

    #[Route('/folder/{id}/delete', name: 'file_folder_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        FileFolder $folder,
        FileFolderRepository $fileFolderRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            FileFolderVoter::DELETE,
            $folder,
            'You cannot delete this folder.'
        );

        $csrfTokenName = 'delete_file_folder_'.$folder->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $fileFolderRepository->remove($folder, true);

            $this->addFlash('notice', 'The folder was deleted successfully.');
        }

        return $this->deleteRedirect($folder);
    }

    protected function deleteRedirect(FileFolder $folder): Response
    {
        if ($parent = $folder->getFolder()) {
            return $this->formRedirect($parent);
        }

        return $this->redirectToRoute('file_index');
    }

    private function getBreadcrumbs(FileFolder $folder): array
    {
        $breadcrumbs = [];

        $loopFolder = $folder->getId() ? $folder : $folder->getFolder();

        while ($loopFolder) {
            $breadcrumbText = $loopFolder->getName();

            $breadcrumbHref = $this->urlGenerator->generate('file_folder_view', [
                'id' => $loopFolder->getId(),
            ]);

            $breadcrumb = new Breadcrumb($breadcrumbText, $breadcrumbHref);

            array_unshift($breadcrumbs, $breadcrumb);

            $loopFolder = $loopFolder->getFolder();
        }

        $indexText = '<i class="bi bi-folder-fill"></i> Files';

        $indexHref = $this->urlGenerator->generate('file_index');

        $indexBreadcrumb = new Breadcrumb($indexText, $indexHref);

        array_unshift($breadcrumbs, $indexBreadcrumb);

        return $breadcrumbs;
    }
}
