<?php 

//Created by Snigdha Sivadas
//create manual  Jan 18

include('../testscript/testscriptdb.php');
require '../../lib/fpdf/fpdf.php'; 
require_once '../common/define_properties.php';

class PDF extends FPDF
{
	function Header()
	{
		global $title;
		
		$this->Image('../../img/menu_left_invader.png',10,6,30);
		// Arial bold 15
		$this->SetFont('Arial','B',15);
		// Calculate width of title and position
		$w = $this->GetStringWidth($title)+6;
		$this->SetX((210-$w)/2);
		// Colors of frame, background and text
		$this->SetDrawColor(0,80,180);
		$this->SetFillColor(230,230,0);
		$this->SetTextColor(220,50,50);
		// Thickness of frame (1 mm)
		$this->SetLineWidth(1);
		// Title
		$this->Cell($w,9,$title,1,1,'C',true);
		// Line break
		$this->Ln(10);
	}

	function Footer()
	{   
		// Position at 1.5 cm from bottom
		$this->SetY(-15);
		// Arial italic 8
		$this->SetFont('Arial','I',8);
		// Text color in gray
		$this->SetTextColor(128);
		// Page number
		$this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
	}

	function ChapterTitle($num, $label)
	{
		// Arial 12
		$this->SetFont('Arial','',12);
		// Background color
		$this->SetTextColor(200,220,255);
		// Title
		$this->Cell(0,6,"Chapter $num : $label",0,1,'L',true);
		// Line break
		$this->Ln(4);
	}

	function ChapterBody($file)
	{
		$limitRight = 100;
		// Read text file
		//$txt = file_get_contents($file);
		$txt= $file['test_methodname'];
		// Times 12
		$this->SetFont('Arial','BUI',18);
		$this->SetTextColor(100,100,0);
		// Output justified text
		$this->MultiCell(0,5,$txt);
		
		// Line break
		$this->Ln();
		
		
		$txt= $file['package_name'];
		// Times 12
		$this->SetFont('Times','',14);
		$this->SetTextColor(180,20,0);
		//$this->MultiCell(0,5,$txt);
		$this->Cell(100,6,$txt,0,1,'L',true);
		// Line break
		$this->Ln();
		
		$this->SetFont('Times','I',14);
		$this->SetTextColor(180,20,0);
		$this->Write(5,'Description:');
		// Line break
		$this->Ln();
		$this->Ln();

		$txt= $file['test_description'];
		// Times 12
		$this->SetFont('Times','',12);
		$this->SetTextColor(180,120,0);
		$this->printTestDetails($txt,$limitRight);
		// Line break
		$this->Ln();
		$this->Ln();
				
		$this->SetFont('Times','I',14);
		$this->SetTextColor(180,20,0);
		$this->Write(5,'Example:');
		$this->Ln();
		$this->Ln();
		
		$txt= $file['test_example'];
		$this->SetFont('Times','',12);
		$this->SetTextColor(180,120,0);
                $this->Write(5,' ');
		$this->printTestDetails($txt,$limitRight);
		
		$this->Ln();
		$this->Ln();
		$this->Ln();
		//$this->Cell(0,5,'(end of excerpt)');
	}

	//function PrintChapter($num, $title, $file)
	function PrintChapter($num, $title)
	{
		$this->AddPage();
		$this->ChapterTitle($num,$title);
		//$this->ChapterBody($file);
	}
	
	
	
	function getArray1($txt){
		$kyit = preg_split("/[\n]/", $txt);
		return $kyit;
	}
	
	function getArray($txt){
		$kyit = preg_split("/[\s]+/", $txt);
		
		return $kyit;
	}
	
	function printTestDetails($txt,$limitRight){
		$rightspace = 10 ;
		$newlinearray = $this->getArray1($txt);
		foreach($newlinearray as $txt1){
			$txtarray= $this->getArray($txt1);
			foreach($txtarray as $txt2){
				$this->Write(5,$txt2);
				$this->Write(5,'  ');
				$rightspace=strlen($txt2)+$rightspace+strlen('  ');
				if ($rightspace > $limitRight ){
					$this->Ln();
					$rightspace = 0;
				}
			}
			$this->Ln();
			$rightspace = 0;
		}
	}
	
	
}




$file_path=TESTDOCS."UserManual.pdf";
system ('touch '.$file_path);
chmod($file_path, 0777);
system ('chmod 777 '.$file_path);
$pdf = new PDF('P','mm','A4');
$title = 'Invader+   Test Library ';
$pdf->SetTitle($title);
$pdf->SetAuthor('Invader+');

$testmodule = new Testmodule();
$data =$testmodule->getMethodManual();


$framework = false;
$page =1;
$loop = 0; 
$current = "";
$chapter =1;
foreach ($data as $arr){
	if($current == "")
		$current = $arr['framework_name'];
	if($current == $arr['framework_name'])
	  	$framework = false;
	else{
	   	$framework = true;
	   	$current = $arr['framework_name'];
	   	$chapter++;
	}
	
	
	if($loop > 1 || $loop == 0 || $framework==true){
		$pdf->PrintChapter($chapter,$arr['framework_name']);
		$page++;
		$loop =0;
	}
	
	$pdf->ChapterBody($arr);
	$loop++;
}

$pdf->Output($file_path);
?>
