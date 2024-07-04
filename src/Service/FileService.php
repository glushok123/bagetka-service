<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class FileService
{
    const PATH_FILE = 'upload/files/';
    const ALLOWED_FILE_TYPES = ['jpg','jpeg', 'png', 'pdf', 'xlsx', 'docx'];
    const MAX_FILE_SIZE = 52428800;

    public function __construct(
        private readonly TranslatorInterface           $translator,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function save(UploadedFile $file): string
    {
        $this->validation($file);

        $filename = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
        $file->move(self::PATH_FILE, $filename);

        return $filename;
    }

    /**
     * @throws Exception
     */
    public function validation(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception($this->translator->trans('file_size_exceeds_the_limit'));
        }

        if (!in_array($file->getClientOriginalExtension(), self::ALLOWED_FILE_TYPES)) {
            throw new Exception($this->translator->trans('invalid_file_type') . ' ' . $file->getClientOriginalExtension());
        }
    }
}
