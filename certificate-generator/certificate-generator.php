<?php
/**
 * Plugin Name: Custom Certificate Generator
 * Plugin URI:  None
 * Description: Generates custom PDF certificates with user information.
 * Version:     1.0
 * Author:      Fadzilah Rafiq
 * Author URI:  linkedin.com/in/fadzilahrafiq
 * License:     GPLv2 or later
 * Text Domain: certificate-generator
 */

//  require_once( plugin_dir_path( __FILE__ ) . 'setaFPDF/autoload.php' );
// require_once( 'setaFPDF/autoload.php' );
// require_once( 'setaFPDF/SetaFpdf.php' );
require_once( 'fpdf186/fpdf.php' );

// use setasign\SetaFpdf\SetaFpdf;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// function generate_certificate( $custom_text ) {

//   // Path to the template PDF file (replace with your actual path)
//   $template_path = 'assets/CERT MYCOR.pdf';
//   echo "<script>console.log('template path -> ', '".$template_path."' );</script>";

//   // Create a new FPDI instance
//   $pdf = new SetaFpdf();

//   // Add a new page with the size of the template
//   $pdf->AddPage(filesize($template_path));

//   // Import the template PDF content onto the newly created page
//   $pdf->setSourceFile($template_path);
//   $template_page = $pdf->ImportPage(1);
//   $pdf->useTemplate($template_page, 0, 0);

//   // Set font and size for the custom text (replace with your desired settings)
//   $pdf->SetFont('Arial', 'B', 16);

//   // Calculate text width
//   $text_width = $pdf->GetStringWidth($custom_text);

//   // Calculate horizontal center position based on page width and text width
//   $x_center = ($pdf->GetPageWidth() - $text_width) / 2;

//   // Adjust vertical position for the text as needed (replace with your desired position)
//   $y_position = 50;

//   // Add the custom text at the center of the first page
//   $pdf->Text($x_center, $y_position, $custom_text);

//   // Generate the final PDF output as a string
//   $pdf_output = $pdf->Output('S');

//   return $pdf_output;

// }

function generate_certificate( $name_text, $cert_text ) {

  $template_path = plugin_dir_path( __FILE__ ) . 'assets/CERT_MYCOR.jpeg';

  // $template_pdf = new FPDF();
  $new_pdf = new FPDF();

  // $template_pdf->setSourceFile($template_path);
  // $template_page = $template_pdf->ImportPage(1);  // Assuming text is on the first page
  // $size = $template_pdf->GetPageSize($template_page);

  $new_pdf->AddPage('L', [3508, 2480], 0 );

  $new_pdf->Image($template_path, 0, 0, 3508, 2480);
  
  $new_pdf->SetFont('Arial', 'B', 300); // Example: Arial, bold, size 16

  $name_width = $new_pdf->GetStringWidth($name_text);
  $cert_width = $new_pdf->GetStringWidth($cert_text);
  $line_spacing = 200;


  $name_x_center = (($new_pdf->GetPageWidth() - $name_width) / 2);  // Calculate horizontal center based on new page width
  $cert_x_center = (($new_pdf->GetPageWidth() - $cert_width) / 2);  // Calculate horizontal center based on new page width
  $y_position = ($new_pdf->GetPageHeight())/2; //50;  // Adjust vertical position as needed
  $new_pdf->Text($name_x_center, $y_position, $name_text);
  $new_pdf->Text($cert_x_center, $y_position + $line_spacing, $cert_text);

  // $pdf_output = $new_pdf->Output('custom_certificate.pdf', 'F'); 
  $pdf_output = $new_pdf->Output('S'); 

  return $pdf_output;

}