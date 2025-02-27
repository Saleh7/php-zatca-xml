<?php
namespace Saleh7\Zatca\Mappers;

use Saleh7\Zatca\AdditionalDocumentReference;
use Saleh7\Zatca\Attachment;

/**
 * Class AdditionalDocumentMapper
 *
 * This class maps additional document reference data (from an array)
 * into an array of AdditionalDocumentReference objects.
 */
class AdditionalDocumentMapper
{
    /**
     * Map additional documents data to an array of AdditionalDocumentReference objects.
     *
     * @param array $documents An array of additional document data.
     *                           Each element may contain keys:
     *                           - id: string (required)
     *                           - uuid: string (optional)
     *                           - attachment: array (optional) with keys:
     *                                 - content: string
     *                                 - mimeCode: string (default: 'base64')
     *                                 - mimeType: string (default: 'text/plain')
     *
     * @return AdditionalDocumentReference[] Array of mapped AdditionalDocumentReference objects.
     */
    public function mapAdditionalDocuments(array $documents): array
    {
        $additionalDocs = [];
        
        foreach ($documents as $doc) {
            // Ensure a valid document ID is provided
            $docId = $doc['id'] ?? '';
            if (empty($docId)) {
                continue; // Skip documents without an ID
            }
            
            $docRef = new AdditionalDocumentReference();
            $docRef->setId($docId);
            
            if (isset($doc['uuid']) && !empty($doc['uuid'])) {
                $docRef->setUUID($doc['uuid']);
            }
            
            // If document ID is 'PIH', map the attachment if provided.
            if ($docId === 'PIH' && isset($doc['attachment']) && is_array($doc['attachment'])) {
                $attachmentData = $doc['attachment'];
                $attachment = (new Attachment())
                    ->setBase64Content(
                        $attachmentData['content'] ?? '',
                        $attachmentData['mimeCode'] ?? 'base64',
                        $attachmentData['mimeType'] ?? 'text/plain'
                    );
                $docRef->setAttachment($attachment);
            }
            
            $additionalDocs[] = $docRef;
        }
        
        // Append a default additional document reference for QR code if not already present.
        $qrExists = false;
        foreach ($additionalDocs as $docRef) {
            if ($docRef->getId() === 'QR') {
                $qrExists = true;
                break;
            }
        }
        if (!$qrExists) {
            $additionalDocs[] = (new AdditionalDocumentReference())->setId('QR');
        }
        
        return $additionalDocs;
    }
}
