<?php

namespace Arall\Converter\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Convert extends Command
{
    public function configure()
    {
        $this
            ->setName('convert:folder')
            ->addArgument(
                'origin',
                InputArgument::REQUIRED,
                'Origin'
            )
            ->addArgument(
                'destination',
                InputArgument::REQUIRED,
                'Destination'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $origin = $input->getArgument('origin');
        $destination = $input->getArgument('destination');

        $videos = glob($origin.'/*.{MP4}', GLOB_BRACE);
        foreach ($videos as $video) {

            // Check if video is already converted
            $basename = basename($video);
            if (!file_exists($destination.$basename)) {
                $progress = $this->getHelper('progress');
                $progress->start($output, 100);
                $output->writeln('<info>Converting '.$basename.'</info>');

                // Convert
                $ffmpeg = \FFMpeg\FFMpeg::create(['timeout' => 0]);
                $ffmpegvideo = $ffmpeg->open($video);

                // Codec
                $format = new \FFMpeg\Format\Video\X264();

                // Progress
                $format->on('progress', function ($ffmpegvideo, $format, $percentage) use ($progress, $output) {
                    // Progress
                    $progress->setCurrent($percentage);
                });

                // Format
                $format
                    ->setKiloBitrate(1200)
                    ->setAudioChannels(2)
                    ->setAudioKiloBitrate(128)
                    ->setAudioCodec('libmp3lame');

                // Resize
                $ffmpegvideo
                    ->filters()
                    ->resize(new \FFMpeg\Coordinate\Dimension(1280, 720))
                    ->synchronize();

                // Convert
                $ffmpegvideo->save($format, $destination.$basename);

                $progress->finish();
            } else {
                $output->writeln('<comment>'.$basename.' already exists</comment>');
            }
        }
    }
}
