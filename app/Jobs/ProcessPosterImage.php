<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

class ProcessPosterImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $concert)
    {
    }

    public function handle()
    {
        $imageContents = Storage::disk('public')->get($this->concert->poster_image_path);
        $image = Image::make($imageContents);

        $image->resize(600, null, function(Constraint $constraint) {
            $constraint->aspectRatio();
        })
        ->limitColors(255)
        ->encode();

        Storage::disk('public')->put($this->concert->poster_image_path, (string)$image);
    }
}
