<?
require_once(_FILES_ABS_.'/tcpdf/config/lang/rus.php');
require_once(_FILES_ABS_.'/tcpdf/tcpdf.php');

class InvoicePdf extends TCPDF{

	private $invoiceData;

	function __construct( $data, $orientation, $unit, $format ) {
		parent::__construct( $orientation, $unit, $format, true, 'UTF-8', false );

		$this->invoiceData = $data;

		# Задаем отступы страницы:
		# 72 пункта слева и справа, 36 пунктов сверху и снизу.
		$this->SetMargins( 72, 36, 72, true );
		$this->SetAutoPageBreak( true, 36 );

		# Указываем метаданные документа
		$this->SetCreator( PDF_CREATOR );
		$this->SetAuthor( 'Promzona.kz' );
		$this->SetTitle( 'Invoice for ' . $this->invoiceData['user'] );
		// $this->SetSubject( "A simple invoice example for 'Creating PDFs on the fly with TCPDF' on IBM's developerWorks" );
		$this->SetKeywords( 'PHP, sample, invoice, PDF, TCPDF' );

		//задаем коэффициент масштабирования изображения
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO); 

		//определяем некоторые строки, зависящие от языка
		global $l;
		$this->setLanguageArray($l);
	}

	#Верхний колонтитул
	public function Header() {
		global $webcolor;

		# Изображение намного больше, чем текст с названием компании.
		$bigFont = 14;
		$imageScale = ( 128.0 / 26.0 ) * $bigFont;
		$smallFont = ( 16.0 / 26.0 ) * $bigFont;

		// $this->ImagePngAlpha('Azuresol_OnyxTree-S.png', 72, 36, $imageScale, 
		// $imageScale, 'PNG', null, 'T', false, 72, 'L' );
		$this->SetFont('times', 'b', $bigFont );
		$this->Cell( 0, 0, 'South Seas Pacifica', 0, 1 );
		$this->SetFont('times', 'i', $smallFont );
		$this->Cell( $imageScale );
		$this->Cell( 0, 0, '', 0, 1 );
		$this->Cell( $imageScale );
		$this->Cell( 0, 0, '31337 Docks Avenue,', 0, 1 );
		$this->Cell( $imageScale );
		$this->Cell( 0, 0, 'Toronto, Ontario', 0, 1 );

		$this->SetY( 1.5 * 72, true );
		$this->SetLineStyle( array( 'width' => 2, 'color' => 
		array( $webcolor['black'] ) ) );
		$this->Line( 72, 36 + $imageScale, $this->getPageWidth() - 72, 36 
		+ $imageScale );
	}

	#Нижний колонтитул
	public function Footer() {
		global $webcolor;

		$this->SetLineStyle( array( 'width' => 2, 'color' => 
		array( $webcolor['black'] ) ) );
		$this->Line( 72, $this->getPageHeight() - 1.5 * 72 - 2, 
		$this->getPageWidth() - 72, $this->getPageHeight() - 1.5 * 72 - 2 );
		$this->SetFont( 'times', '', 8 );
		$this->SetY( -1.5 * 72, true );
		$this->Cell( 72, 0, 'Invoice prepared for ' . 
		$this->invoiceData['user'] . ' on ' . $this->invoiceData['date'] );
	}

	#Создание pdf
	public function CreateInvoice() {
		$this->AddPage();
		$this->SetFont( 'helvetica', '', 11 );
		$this->SetY( 144, true );

		# Параметры таблицы
		#
		# Размер, ширина (описание) столбца, отступ таблицы, высота строки.
		$col = 72;
		$wideCol = 3 * $col;
		$indent = ( $this->getPageWidth() - 2 * 72 - $wideCol - 3 * $col ) / 2;
		$line = 18;

		# Заголовок таблицы
		$this->SetFont( '', 'b' );
		$this->Cell( $indent );
		$this->Cell( $wideCol, $line, 'Item', 1, 0, 'L' );
		$this->Cell( $col, $line, 'Quantity', 1, 0, 'R' );
		$this->Cell( $col, $line, 'Price', 1, 0, 'R' );
		$this->Cell( $col, $line, 'Cost', 1, 0, 'R' );
		$this->Ln();

		# Строки с содержимым таблицы
		$this->SetFont( '', '' );
		foreach( $this->invoiceData['items'] as $item ) {
		    $this->Cell( $indent );
		    $this->Cell( $wideCol, $line, $item[0], 1, 0, 'L' );
		    $this->Cell( $col, $line, $item[1], 1, 0, 'R' );
		    $this->Cell( $col, $line, $item[2], 1, 0, 'R' );
		    $this->Cell( $col, $line, $item[3], 1, 0, 'R' );
		    $this->Ln();
		}

		# Итоговая строка таблицы (сумма заказа)
		$this->SetFont( '', 'b' );
		$this->Cell( $indent );
		$this->Cell( $wideCol + $col * 2, $line, 'Total:', 1, 0, 'R' );
		$this->SetFont( '', '' );
		$this->Cell( $col, $line, $this->invoiceData['total'], 1, 0, 'R' );
	}


}


?>