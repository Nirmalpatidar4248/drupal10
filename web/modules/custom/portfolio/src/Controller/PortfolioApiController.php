<?php

namespace Drupal\portfolio\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\HttpFoundation\Response;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;

class PortfolioApiController extends ControllerBase
{
    public function getPortfolioData() {
        $nodes = Node::loadMultiple();
        // dd($nodes);
        $nodes_profile = [];
        $file_url_generator = \Drupal::service("file_url_generator");
        foreach ($nodes as $profilenode) {
          if ($profilenode->getType() === "port") {
    
            $image_url = null;
            if (!$profilenode->field_profile_picture->isEmpty()) {
              $file = $profilenode->field_profile_picture->entity;
              if ($file) {
                $image_url = $file_url_generator->generateAbsoluteString($file->getFileUri());
              }
            }
    
            $paragraph_data = [];
            if (!$profilenode->field_projects->isEmpty()) {
              $paragraph_field = $profilenode->field_projects;
              // dd($paragraph_field);
              foreach ($paragraph_field as $paragraph_item) {
                $paragraph = $paragraph_item->entity;
                // dd($paragraph);
                if ($paragraph) {
                  $paragraph_title = $paragraph->field_titlee->value;
                  $paragraph_desc = $paragraph->field_description->value;
                  // $project_link = $paragraph->get('field_website_link')->uri;

                  if ($paragraph->hasField('field_website_link') && !$paragraph->get('field_website_link')->isEmpty()) {
                    // Get the link URI (URL or internal path).
                    $project_link = $paragraph->get('field_website_link')->uri;
                    // dd($project_link);
                    // Get the link title (if set).
                    $project_title = $paragraph->get('field_website_link')->title;
                  } 
                  $image = $paragraph->field_images->entity;
                  $project_image_url = null;
                  if ($image) {
                    $project_image_url = $file_url_generator->generateAbsoluteString($image->getFileUri());
                  }
    
                  $paragraph_data[] = [
                    "title" => $paragraph_title,
                    "image_url" => $project_image_url,
                    "description" => $paragraph_desc,
                    "link" => $project_link,
                  ];
                }
              }
            }
    
            $nodes_profile[] = [
              "id" => $profilenode->id(),
              "profilePic" => $image_url,
              "title" => $profilenode->getTitle(),
              "firstname" => $profilenode->field_firstname->value,
              "lastname" => $profilenode->field_lastname->value,
              "email" => $profilenode->field_email->value,
              "mobile_number" => $profilenode->field_mobile_number->value,
              "address" => $profilenode->field_address->value,
              "gender" => $profilenode->field_gender->value,
              "birth_date" => $profilenode->field_birth_date->value,
              "short_bio" => $profilenode->field_short_bio->value,
              "projects_detail" => $paragraph_data,
            ];
          }
        }
      //  dd($nodes_profile);
        return new JsonResponse($nodes_profile);
      }
    }
  
