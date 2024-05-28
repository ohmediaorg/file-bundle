<?php

namespace OHMedia\FileBundle\Controller\Backend;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Component\Breadcrumb;
use OHMedia\FileBundle\Entity\File;
use OHMedia\FileBundle\Entity\FileFolder;
use OHMedia\FileBundle\Form\Type\FileCreateType;
use OHMedia\FileBundle\Form\Type\FileEditType;
use OHMedia\FileBundle\Form\Type\FileMoveType;
use OHMedia\FileBundle\Repository\FileRepository;
use OHMedia\FileBundle\Security\Voter\FileVoter;
use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\FileBundle\Util\FileUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Admin]
class FileController extends AbstractController
{
    public function __construct(
        private FileBrowser $fileBrowser,
        private FileRepository $fileRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[Route('/files', name: 'file_index', methods: ['GET'])]
    public function index(FileBrowser $fileBrowser): Response
    {
        $newFile = (new File())->setBrowser(true);
        $newFolder = (new FileFolder())->setBrowser(true);

        $this->denyAccessUnlessGranted(
            FileVoter::INDEX,
            $newFile,
            'You cannot access the list of files.'
        );

        $items = $fileBrowser->getListing();

        return $this->render('@OHMediaFile/file/file_index.html.twig', [
            'items' => $items,
            'new_file' => $newFile,
            'new_folder' => $newFolder,
        ]);
    }

    #[Route('/file/create', name: 'file_create_no_folder', methods: ['GET', 'POST'])]
    public function fileCreateNoFolder(Request $request): Response
    {
        $file = (new File())->setBrowser(true);

        return $this->create($request, $file);
    }

    #[Route('/folder/{id}/file/create', name: 'file_create_with_folder', methods: ['GET', 'POST'])]
    public function fileCreateWithFolder(
        Request $request,
        FileFolder $folder
    ): Response {
        $file = (new File())->setBrowser(true);

        $folder->addFile($file);

        return $this->create($request, $file);
    }

    #[Route('/image/create', name: 'image_create_no_folder', methods: ['GET', 'POST'])]
    public function imageCreateNoFolder(Request $request): Response
    {
        $file = (new File())->setBrowser(true)->setImage(true);

        return $this->create($request, $file);
    }

    #[Route('/folder/{id}/image/create', name: 'image_create_with_folder', methods: ['GET', 'POST'])]
    public function imageCreateWithFolder(
        Request $request,
        FileFolder $folder
    ): Response {
        $file = (new File())->setBrowser(true)->setImage(true);

        $folder->addFile($file);

        return $this->create($request, $file);
    }

    private function create(
        Request $request,
        File $file
    ): Response {
        $noun = $file->isImage() ? 'image' : 'file';

        $this->denyAccessUnlessGranted(
            FileVoter::CREATE,
            $file,
            "You cannot create a new $noun."
        );

        $usage = $this->fileBrowser->getUsageBytes();

        $limit = $this->fileBrowser->getLimitBytes();

        $remainingBytes = $limit - $usage;

        $maxSize = ini_get('upload_max_filesize');

        $maxSizeBytes = FileUtil::getBytes($maxSize);

        if ($maxSizeBytes > $remainingBytes) {
            $maxSizeBytes = $remainingBytes;
        }

        $form = $this->createForm(FileCreateType::class, $file, [
            'max_size_bytes' => $maxSizeBytes,
        ]);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->fileRepository->save($file, true);

            $this->addFlash('notice', "The $noun was created successfully.");

            return $this->formRedirect($file);
        }

        $breadcrumbs = $this->getBreadcrumbs($file);

        $breadcrumbs[] = new Breadcrumb('Create');

        return $this->render('@OHMediaFile/file/file_form.html.twig', [
            'form' => $form->createView(),
            'file' => $file,
            'form_title' => $file->isImage() ? 'Create Image' : 'Create File',
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/file/{id}/edit', name: 'file_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        File $file
    ): Response {
        $this->denyAccessUnlessGranted(
            FileVoter::EDIT,
            $file,
            'You cannot edit this image.'
        );

        $form = $this->createForm(FileEditType::class, $file);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->fileRepository->save($file, true);

            $this->addFlash('notice', 'The image was edited successfully.');

            return $this->formRedirect($file);
        }

        $breadcrumbs = $this->getBreadcrumbs($file);

        $breadcrumbs[] = new Breadcrumb('Edit');

        return $this->render('@OHMediaFile/file/file_form.html.twig', [
            'form' => $form->createView(),
            'file' => $file,
            'form_title' => $file->isImage() ? 'Edit Image' : 'Edit File',
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/file/{id}/move', name: 'file_move', methods: ['GET', 'POST'])]
    public function move(
        Request $request,
        File $file,
    ): Response {
        $noun = $file->isImage() ? 'image' : 'file';

        $this->denyAccessUnlessGranted(
            FileVoter::MOVE,
            $file,
            "You cannot move this $noun."
        );

        $form = $this->createForm(FileMoveType::class, $file);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->fileRepository->save($file, true);

            $this->addFlash('notice', "The $noun was moved successfully.");

            return $this->formRedirect($file);
        }

        $breadcrumbs = $this->getBreadcrumbs($file);

        $breadcrumbs[] = new Breadcrumb('Move');

        return $this->render('@OHMediaFile/file/file_form.html.twig', [
            'form' => $form->createView(),
            'file' => $file,
            'form_title' => $file->isImage() ? 'Move Image' : 'Move File',
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    #[Route('/file/{id}/lock', name: 'file_lock', methods: ['POST'])]
    public function lock(
        Request $request,
        File $file,
    ): Response {
        $noun = $file->isImage() ? 'image' : 'file';

        $this->denyAccessUnlessGranted(
            FileVoter::LOCK,
            $file,
            "You cannot lock this $noun."
        );

        $csrfTokenName = 'lock_file_'.$file->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $file->setLocked(true);

            $this->fileRepository->save($file, true);

            $this->addFlash('notice', "The $noun was locked successfully.");
        }

        return $this->formRedirect($file);
    }

    #[Route('/file/{id}/unlock', name: 'file_unlock', methods: ['POST'])]
    public function unlock(
        Request $request,
        File $file,
    ): Response {
        $noun = $file->isImage() ? 'image' : 'file';

        $this->denyAccessUnlessGranted(
            FileVoter::UNLOCK,
            $file,
            "You cannot unlock this $noun."
        );

        $csrfTokenName = 'unlock_file_'.$file->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $file->setLocked(false);

            $this->fileRepository->save($file, true);

            $this->addFlash('notice', "The $noun was unlocked successfully.");
        }

        return $this->formRedirect($file);
    }

    protected function formRedirect(File $file): Response
    {
        if ($folder = $file->getFolder()) {
            return $this->redirectToRoute('file_folder_view', [
                'id' => $folder->getId(),
            ]);
        }

        return $this->redirectToRoute('file_index');
    }

    #[Route('/file/{id}/delete', name: 'file_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        File $file,
    ): Response {
        $noun = $file->isImage() ? 'image' : 'file';

        $this->denyAccessUnlessGranted(
            FileVoter::DELETE,
            $file,
            "You cannot delete this $noun."
        );

        $csrfTokenName = 'delete_file_'.$file->getId();
        $csrfTokenValue = $request->request->get($csrfTokenName);

        if ($this->isCsrfTokenValid($csrfTokenName, $csrfTokenValue)) {
            $this->fileRepository->remove($file, true);

            $this->addFlash('notice', "The $noun was deleted successfully.");
        }

        return $this->deleteRedirect($file);
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

    private function getBreadcrumbs(File $file): array
    {
        $breadcrumbs = [];

        if ($file->getId()) {
            $breadcrumbs[] = new Breadcrumb($file->getFilename());
        }

        $loopFolder = $file->getFolder();

        while ($loopFolder) {
            $breadcrumbText = $loopFolder->getName();

            $breadcrumb = new Breadcrumb($breadcrumbText, 'file_folder_view', [
                'id' => $loopFolder->getId(),
            ]);

            array_unshift($breadcrumbs, $breadcrumb);

            $loopFolder = $loopFolder->getFolder();
        }

        $indexText = '<i class="bi bi-folder-fill"></i> Files';

        $indexBreadcrumb = new Breadcrumb($indexText, 'file_index');

        array_unshift($breadcrumbs, $indexBreadcrumb);

        return $breadcrumbs;
    }
}
