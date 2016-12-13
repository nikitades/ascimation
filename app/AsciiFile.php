<?php

namespace App;

class AsciiFile extends File
{
    protected $table = 'ascii_files';

    public function scopeAttachmentTo($query, $id)
    {
        $select_fields = [
            'files.id',
            'files.name',
            'files.filename'
        ];
        return $query->where('parent_id', $id)
            ->orderBy('pos')
            ->join('files', $this->table . '.file_id', '=', 'files.id')
            ->select($select_fields);
    }
}
