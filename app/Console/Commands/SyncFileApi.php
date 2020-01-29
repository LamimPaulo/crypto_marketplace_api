<?php

namespace App\Console\Commands;

use App\Models\User\Document;
use App\Models\User\UserTicketFile;
use App\Services\FileApiService;
use Illuminate\Console\Command;

class SyncFileApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fileapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar nova api de arquivos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();

        try {

            $_files = FileApiService::files();
            for ($i = 1; $i <= $_files['last_page']; $i++) {
                $files = FileApiService::files($i);
                foreach ($files['data'] as $file) {

                    $output->writeln("<info>-----------------------</info>");
                    $document = Document::where('path', "LIKE", $file['file'])->first();

                    if ($document) {
                        $document->api_id = $file['id'];
                        $document->save();
                        $output->writeln("<info>{$file['file']}</info>");
                    } else {
                        $ticketFile = UserTicketFile::where('file', "LIKE", $file['file'])->first();
                        if ($ticketFile) {
                            $ticketFile->api_id = $file['id'];
                            $ticketFile->type = $file['type'];
                            $ticketFile->save();
                            $output->writeln("<info>{$file['file']}</info>");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $output->writeln("<info>{$e->getMessage()}</info>");
            $output->writeln("<info>{$e->getLine()} - {$e->getFile()}</info>");
        }
    }
}
