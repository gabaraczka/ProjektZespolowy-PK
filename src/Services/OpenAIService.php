<?php

namespace App\Services;

use OpenAI;

class OpenAIService {
    private $client;
    private $hasApiKey;
    
    public function __construct() {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');
        

        if (!$apiKey) {
            $apiKey = "";
        }
        
        if ($apiKey === 'your-openai-api-key-here' || empty($apiKey)) {
            $this->hasApiKey = false;
            $this->client = null;
        } else {
            $this->hasApiKey = true;
            $this->client = OpenAI::client($apiKey);
        }
    }
    
    public function generateSummary(string $filePath, string $fileName): string {
        if (!$this->hasApiKey) {
            return "OpenAI API key not configured. This is a placeholder summary for: " . $fileName;
        }
        
        try {
            $content = $this->extractFileContent($filePath);
            
            if (empty($content)) {
                return "Could not extract content from file: " . $fileName;
            }
            
            $prompt = "Extract the most important information from the file and make a summary of it. Easy-to-learn summary:\n\n" . $content;
            
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);
            
            return $response->choices[0]->message->content;
            
        } catch (\Exception $e) {
            return "Error generating summary: " . $e->getMessage();
        }
    }
    
    public function generateSummaryFromText(string $text, string $title): string {
        if (!$this->hasApiKey) {
            return "OpenAI API key not configured. This is a placeholder summary for note: " . $title;
        }
        
        try {
            $prompt = "Extract the most important information from this note and make a summary of it. Easy-to-learn summary:\n\n" . $text;
            
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 300,
                'temperature' => 0.7,
            ]);
            
            return $response->choices[0]->message->content;
            
        } catch (\Exception $e) {
            return "Error generating summary: " . $e->getMessage();
        }
    }
    
    public function generateText(string $prompt): string {
        if (!$this->hasApiKey) {
            return "OpenAI API key not configured.";
        }
        
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);
            
            return $response->choices[0]->message->content;
            
        } catch (\Exception $e) {
            return "Error generating text: " . $e->getMessage();
        }
    }
    
    public function extractFileContent(string $filePath): string {
        if (!file_exists($filePath)) {
            return '';
        }
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'txt':
                return file_get_contents($filePath);
                
            case 'pdf':
                return $this->extractPdfContent($filePath);
                
            case 'doc':
            case 'docx':
                return $this->extractDocContent($filePath);
                
            default:
                return '';
        }
    }
    
    private function extractPdfContent(string $filePath): string {
        $command = "pdftotext '$filePath' -";
        $output = shell_exec($command);
        
        if ($output === null) {
            return "Zainstaluj pdftotext.";
        }
        
        return $output;
    }
    
    private function extractDocContent(string $filePath): string {

        return "Ekstrakcja zawartości DOC/DOCX nie jest jeszcze zaimplementowana. Użyj plików TXT lub PDF.";
    }
} 