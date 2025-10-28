<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\PlantScanResultWithImageMail;

class PlantScanEmailTest extends TestCase
{
    // No database needed for this email endpoint test

    /** @test */
    public function it_can_queue_email_with_image_attachment()
    {
        Mail::fake();

        // Create a fake PNG image
        $image = UploadedFile::fake()->image('plantscan-result.png', 1080, 1920)->size(1024);

        $response = $this->postJson('/plantscan/email', [
            'email' => 'test@example.com',
            'pet_name' => 'Luna',
            'plant_name' => 'Monstera Deliciosa',
            'image' => $image,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Assert email was queued
        Mail::assertQueued(PlantScanResultWithImageMail::class, function ($mail) {
            return $mail->hasTo('test@example.com')
                && isset($mail->payload['pet_name'])
                && $mail->payload['pet_name'] === 'Luna'
                && isset($mail->payload['image_contents']);
        });
    }

    /** @test */
    public function it_validates_required_email_field()
    {
        $image = UploadedFile::fake()->image('test.png');

        $response = $this->postJson('/plantscan/email', [
            'image' => $image,
            // email missing
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_required_image_field()
    {
        $response = $this->postJson('/plantscan/email', [
            'email' => 'test@example.com',
            // image missing
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function it_validates_image_file_type()
    {
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/plantscan/email', [
            'email' => 'test@example.com',
            'image' => $invalidFile,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function it_validates_image_file_size()
    {
        // Create image larger than 5MB (5120KB)
        $largeImage = UploadedFile::fake()->image('large.png')->size(6000);

        $response = $this->postJson('/plantscan/email', [
            'email' => 'test@example.com',
            'image' => $largeImage,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }
}
