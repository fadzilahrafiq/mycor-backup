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

 require_once( plugin_dir_path( __FILE__ ) . 'fpdf/fpdf.php' );

 function generate_certificate( $custom_text ) {

  // Path to the template PDF file (replace with your actual path)
  $template_path = plugin_dir_path( __FILE__ ) . 'assets/CERT MYCOR.pdf';

  // Create a new FPDI instance
  $pdf = new FPDI();

  // Add a new page with the size of the template
  $pdf->AddPage(filesize($template_path));

  // Import the template PDF content onto the newly created page
  $pdf->setSourceFile($template_path);
  $template_page = $pdf->ImportPage(1);
  $pdf->useTemplate($template_page, 0, 0);

  // Set font and size for the custom text (replace with your desired settings)
  $pdf->SetFont('Arial', 'B', 16);

  // Calculate text width
  $text_width = $pdf->GetStringWidth($custom_text);

  // Calculate horizontal center position based on page width and text width
  $x_center = ($pdf->GetPageWidth() - $text_width) / 2;

  // Adjust vertical position for the text as needed (replace with your desired position)
  $y_position = 50;

  // Add the custom text at the center of the first page
  $pdf->Text($x_center, $y_position, $custom_text);

  // Generate the final PDF output as a string
  $pdf_output = $pdf->Output('S');

  return $pdf_output;

}
