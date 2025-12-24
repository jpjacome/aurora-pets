<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Seeder;

class WhatsAppChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a random client for testing (if exists)
        $client = Client::first();

        // Create sample conversations
        $conversations = [
            [
                'phone_number' => '+593991234567',
                'contact_name' => 'María González',
                'client_id' => $client?->id,
                'is_bot_mode' => true,
                'lead_score' => 'warm',
                'last_message_at' => now()->subMinutes(30),
                'unread_count' => 2,
                'messages' => [
                    [
                        'direction' => 'incoming',
                        'content' => 'Hola, me gustaría saber más sobre sus servicios de paisajismo',
                        'created_at' => now()->subMinutes(45),
                    ],
                    [
                        'direction' => 'outgoing',
                        'content' => '¡Hola María! Gracias por contactarnos. En Aurora ofrecemos diseño de jardines personalizados, PlantScan para encontrar la planta perfecta para tu mascota, y mantenimiento de espacios verdes. ¿Qué servicio te interesa más?',
                        'sent_by_bot' => true,
                        'status' => 'read',
                        'created_at' => now()->subMinutes(44),
                    ],
                    [
                        'direction' => 'incoming',
                        'content' => 'Me interesa el PlantScan, tengo un perro y quiero plantas seguras',
                        'created_at' => now()->subMinutes(40),
                    ],
                    [
                        'direction' => 'outgoing',
                        'content' => 'Perfecto! PlantScan es ideal para ti. Te ayudamos a crear un jardín hermoso y 100% seguro para tu perro. ¿Te gustaría agendar una cita para conocer tu espacio?',
                        'sent_by_bot' => true,
                        'status' => 'delivered',
                        'created_at' => now()->subMinutes(39),
                    ],
                    [
                        'direction' => 'incoming',
                        'content' => 'Sí, ¿cuándo tienen disponibilidad?',
                        'created_at' => now()->subMinutes(30),
                    ],
                ],
            ],
            [
                'phone_number' => '+593987654321',
                'contact_name' => 'Carlos Ramírez',
                'is_bot_mode' => false,
                'lead_score' => 'hot',
                'last_message_at' => now()->subHours(2),
                'unread_count' => 0,
                'messages' => [
                    [
                        'direction' => 'incoming',
                        'content' => 'Buenos días, vi su página web y me interesa un diseño de jardín completo',
                        'created_at' => now()->subHours(3),
                    ],
                    [
                        'direction' => 'outgoing',
                        'content' => 'Buenos días Carlos! Excelente. ¿Ya tienes un espacio definido? ¿Cuántos metros cuadrados aproximadamente?',
                        'sent_by_bot' => false,
                        'status' => 'read',
                        'created_at' => now()->subHours(2, 50),
                    ],
                    [
                        'direction' => 'incoming',
                        'content' => 'Sí, tengo unos 50m2 en el patio trasero',
                        'created_at' => now()->subHours(2, 45),
                    ],
                    [
                        'direction' => 'outgoing',
                        'content' => 'Perfecto, es un buen espacio. Te envío mi calendario para coordinar una visita técnica. ¿Te parece bien mañana en la tarde?',
                        'sent_by_bot' => false,
                        'status' => 'read',
                        'created_at' => now()->subHours(2, 30),
                    ],
                    [
                        'direction' => 'incoming',
                        'content' => 'Perfecto, mañana a las 3pm está bien',
                        'created_at' => now()->subHours(2),
                    ],
                ],
            ],
            [
                'phone_number' => '+593999888777',
                'contact_name' => 'Ana Flores',
                'is_bot_mode' => true,
                'lead_score' => 'new',
                'last_message_at' => now()->subDays(1),
                'unread_count' => 0,
                'messages' => [
                    [
                        'direction' => 'incoming',
                        'content' => 'Hola',
                        'created_at' => now()->subDays(1),
                    ],
                    [
                        'direction' => 'outgoing',
                        'content' => '¡Hola! Bienvenida a Aurora. ¿En qué podemos ayudarte hoy?',
                        'sent_by_bot' => true,
                        'status' => 'delivered',
                        'created_at' => now()->subDays(1)->addMinutes(1),
                    ],
                ],
            ],
            [
                'phone_number' => '+593981122334',
                'is_bot_mode' => true,
                'lead_score' => 'cold',
                'last_message_at' => now()->subDays(7),
                'unread_count' => 0,
                'messages' => [
                    [
                        'direction' => 'incoming',
                        'content' => 'Información por favor',
                        'created_at' => now()->subDays(7),
                    ],
                    [
                        'direction' => 'outgoing',
                        'content' => '¡Hola! En Aurora ofrecemos: 🌿 Diseño de jardines personalizados 🐕 PlantScan (plantas seguras para mascotas) 🌱 Mantenimiento de espacios verdes. ¿Qué servicio te interesa?',
                        'sent_by_bot' => true,
                        'status' => 'sent',
                        'created_at' => now()->subDays(7)->addMinutes(2),
                    ],
                ],
            ],
        ];

        foreach ($conversations as $conversationData) {
            $messages = $conversationData['messages'];
            unset($conversationData['messages']);

            $conversation = WhatsAppConversation::create($conversationData);

            foreach ($messages as $messageData) {
                $conversation->messages()->create($messageData);
            }
        }

        $this->command->info('WhatsApp chatbot test data created successfully!');
        $this->command->info('Created ' . count($conversations) . ' conversations with messages.');
    }
}
