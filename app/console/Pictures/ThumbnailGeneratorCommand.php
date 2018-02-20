<?php

namespace App\Console;

use App\Model\IO;
use App\Model\Thumbnail;
use Nette\Database\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThumbnailGeneratorCommand extends Command
{
    /** @var Context */
    public $database;

    public function __construct(Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * php www/index.php pictures:thumbnail-generator
     */
    protected function configure(): void
    {
        $this->setName('pictures:thumbnail-generator')
            ->setDescription('Sends the newsletter');
    }

    public function getSettings()
    {
        return $this->database->table('settings')->fetchPairs('setkey', 'setvalue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $pictures = $this->database->table('pictures');

            foreach ($pictures as $picture) {
                IO::directoryMake(APP_DIR . '/pictures/' . $picture->pages_id . '/tn');

                if (file_exists(APP_DIR . '/pictures/' . $picture->pages_id . '/' . $picture->name) &&
                    !file_exists(APP_DIR . '/pictures/' . $picture->pages_id . '/tn/' . $picture->name)) {

                    $thumb = new Thumbnail;
                    $thumb->setFile('/pictures/' . $picture->pages_id, $picture->name);
                    $thumb->setDimensions($this->getSettings()['media_thumb_width'],
                        $this->getSettings()['media_thumb_height']);
                    $thumb->save($this->getSettings()['media_thumb_dir']);

                    $output->writeln($picture->name . ' thumbnail created');
                }
            }

            $output->writeln('<comment>Finished</comment>');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}