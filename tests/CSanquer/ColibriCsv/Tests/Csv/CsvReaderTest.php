<?php

namespace CSanquer\ColibriCsv\Tests\Csv;

use CSanquer\ColibriCsv\Tests\AbstractCsvTestCase;
use CSanquer\ColibriCsv\CsvReader;

/**
 * CsvReaderTest
 *
 * @author Charles SANQUER - <charles.sanquer@gmail.com>
 */
class CsvReaderTest extends AbstractCsvTestCase
{
    /**
     *
     * @var \CSanquer\ColibriCsv\CsvReader
     */
    protected $reader;

    protected function setUp()
    {
        $this->reader = new CsvReader();
    }

    public function testConstruct()
    {
        $this->assertEquals('rb', $this->getFileHandlerModeValue($this->reader));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not open file "" for reading.
     */
    public function testReadingNoFilename()
    {
        $actual = array();
        foreach ($this->reader as $key => $value) {
            $actual[] = $value;
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A valid file handler resource must be passed as parameter.
     */
    public function testReadingNoFileHandler()
    {
        $this->reader->next();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The file "foobar.csv" does not exists.
     */
    public function testReadingFilenameInvalid()
    {
        $this->reader->open('foobar.csv');
    }

    /**
     * @dataProvider providerCount
     */
    public function testCount($options, $filename, $expected)
    {
        $this->reader = new CsvReader($options);
        $this->reader->setFilename($filename);
        $this->assertEquals($expected, count($this->reader));
    }

    public function providerCount()
    {
        return array(
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test1.csv',
                3
            ),
            array(
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => 'CP1252',
                    'eol' => "\r\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test2.csv',
                4
            ),
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test3.csv',
                3
            ),
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test4.csv',
                4
            ),
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => true,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test5_bom.csv',
                3
            ),
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test6.csv',
                3
            ),
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'double_enclosure' => true,
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test7.csv',
                4
            ),
        );
    }

    /**
     * @dataProvider providerReading
     */
    public function testReading($options, $filename, $expected)
    {
        $this->reader = new CsvReader($options);
        $this->assertInstanceOf('CSanquer\ColibriCsv\CsvReader', $this->reader->open($filename));

        $actual1 = array();
        $i = 0;
        foreach ($this->reader as $key => $value) {
            $i++;
            $actual1[] = $value;
        }

        $actual2 = $this->reader->getRows();

        $this->reader->reset();
        $actual3 = array();
        while ($row = $this->reader->getRow()) {
            $actual3[] = $row;
        }

        $this->assertEquals($expected, $actual1);
        $this->assertEquals($expected, $actual2);
        $this->assertEquals($expected, $actual3);
        $this->assertInstanceOf('CSanquer\ColibriCsv\CsvReader', $this->reader->close());
    }

    public function providerReading()
    {
        return array(
            //data set #0
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test1.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Martin', 'Durand', '28'),
                    array('Alain', 'Richard', '36'),
                )
            ),
            //data set #1
            array(
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => 'CP1252',
                    'eol' => "\r\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test2.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Bousquet', 'Inès', '32'),
                    array('Morel', 'Monique', '41'),
                    array('Gauthier', 'Aurélie', '24'),
                )
            ),
            //data set #2
            array(
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => '',
                    'eol' => "\r\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => true,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test2.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Bousquet', 'Inès', '32'),
                    array('Morel', 'Monique', '41'),
                    array('Gauthier', 'Aurélie', '24'),
                )
            ),
            //data set #3
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => true,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test3.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Martin', 'Durand', '28'),
                    array('Alain', 'Richard', '36'),
                )
            ),
            //data set #4
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => true,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test4.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Martin', 'Durand', '28'),
                    array('Alain', 'Richard', '36'),
                    array('Dupont', '', ''),
                )
            ),
            //data set #5
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => true,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test5_bom.csv', //file UTF8 with BOM
                array(
                    array('nom', 'prénom', 'age'),
                    array('Martin', 'Durand', '28'),
                    array('Alain', 'Richard', '36'),
                )
            ),
            //data set #6
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test6.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Martin', 'Durand', '28'),
                    array('Alain', '  Richard ', '36  '),
                )
            ),
            //data set #7
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => true,
                    'trim' => true,
                ),
                __DIR__.'/../Fixtures/test6.csv',
                array(
                    array('nom', 'prénom', 'age'),
                    array('Martin', 'Durand', '28'),
                    array('Alain', 'Richard', '36'),
                )
            ),
            //data set #8
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'double_enclosure' => true,
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test7.csv',
                array(
                    array('nom', 'prénom', 'desc', 'age'),
                    array('Martin', 'Durand
 test', '"5\'10""', '28'),
                    array('Alain', 'Richard', '"5\'30""', '36'),
                    array('Paul', 'Henri', '"4\'80""', '22'),
                )
            ),
            //data set #9
            array(
                array(
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test8.csv',
                array(
                    array('nom', 'prénom', 'desc', 'age'),
                    array('Martin', 'Durand', 'test" a', '28'),
                    array('Alain', 'Richard', 'test"" b', '36'),
                )
            ),
            //data set #9 Don' use detect encoding and don't set encoding to ASCII instead of UTF8
            array(
                array(
                    'delimiter' => ';',
                    'enclosure' => '"',
                    'encoding' => 'UTF-8',
                    'eol' => "\n",
                    'escape' => "\\",
                    'bom' => false,
                    'translit' => 'translit',
                    'force_encoding_detect' => false,
                    'skip_empty' => false,
                    'trim' => false,
                ),
                __DIR__.'/../Fixtures/test9.csv',
                array(
                    array('nom', 'prenom', 'email'),
                    array('Ledoux', 'Patrick', 'ledoux.patrick@example.com'),
                    array('Didier', 'Guy', 'didier.guy@example.fr'),
                    array('Ferrand', 'Penelope', 'ferrand.penelope@example.fr'),
                    array('Julien', 'Pauline', 'julien.pauline@example.fr'),
                    array('Daniel', 'Lucie', 'daniel.lucie@example.com'),
                    array('Hernandez', 'Martin', 'hernandez.martin@example.fr'),
                    array('Hernandez', 'Joseph', 'hernandez.joseph@example.fr'),
                    array('Pineau', 'Guillaume', 'pineau.guillaume@example.net'),
                    array('David', 'Christine', 'david.christine@example.fr'),
                    array('Bernier', 'David', 'bernier.david@example.fr'),
                    array('Fernandes', 'Penelope', 'fernandes.penelope@example.fr'),
                    array('Pruvost', 'Oceane', 'pruvost.oceane@example.com'),
                    array('Menard', 'Sylvie', 'menard.sylvie@example.com'),
                    array('Boyer', 'Franck', 'boyer.franck@example.fr'),
                    array('Dos Santos', 'Alix', 'dos santos.alix@example.fr'),
                    array('Dias', 'Amelie', 'dias.amelie@example.fr'),
                    array('Gregoire', 'Dominique', 'gregoire.dominique@example.fr'),
                    array('Bernier', 'Roland', 'bernier.roland@example.fr'),
                    array('Leroy', 'Remy', 'leroy.remy@example.fr'),
                    array('Lopes', 'Nicole', 'lopes.nicole@example.fr'),
                    array('Legrand', 'Paulette', 'legrand.paulette@example.fr'),
                    array('Bonnet', 'Guy', 'bonnet.guy@example.fr'),
                    array('Tanguy', 'Alphonse', 'tanguy.alphonse@example.fr'),
                    array('Monnier', 'Arthur', 'monnier.arthur@example.fr'),
                    array('Ruiz', 'Guillaume', 'ruiz.guillaume@example.fr'),
                    array('Dias', 'Odette', 'dias.odette@example.fr'),
                    array('Lacroix', 'elodie', 'lacroix.elodie@example.com'),
                    array('Hebert', 'Remy', 'hebert.remy@example.fr'),
                    array('Lelievre', 'Genevieve', 'lelievre.genevieve@example.fr'),
                    array('Letellier', 'Olivier', 'letellier.olivier@example.fr'),
                    array('Leduc', 'Adelaide', 'leduc.adelaide@example.net'),
                    array('Rodriguez', 'Frederique', 'rodriguez.frederique@example.com'),
                    array('Besson', 'Marguerite', 'besson.marguerite@example.com'),
                    array('Menard', 'Sebastien', 'menard.sebastien@example.com'),
                    array('Riviere', 'Emmanuel', 'riviere.emmanuel@example.fr'),
                    array('Gimenez', 'Zacharie', 'gimenez.zacharie@example.net'),
                    array('Aubert', 'Agnes', 'aubert.agnes@example.fr'),
                    array('Gallet', 'Timothee', 'gallet.timothee@example.net'),
                    array('Payet', 'William', 'payet.william@example.net'),
                    array('Vallet', 'Alex', 'vallet.alex@example.com'),
                    array('Hubert', 'Guy', 'hubert.guy@example.net'),
                    array('De Oliveira', 'Leon', 'de oliveira.leon@example.com'),
                    array('Blot', 'Suzanne', 'blot.suzanne@example.fr'),
                    array('De Sousa', 'Theophile', 'de sousa.theophile@example.fr'),
                    array('Delorme', 'elodie', 'delorme.elodie@example.fr'),
                    array('Pinto', 'Guillaume', 'pinto.guillaume@example.com'),
                    array('Duval', 'Luc', 'duval.luc@example.net'),
                    array('Vincent', 'Henriette', 'vincent.henriette@example.fr'),
                    array('Dumas', 'elodie', 'dumas.elodie@example.net'),
                    array('Humbert', 'Mathilde', 'humbert.mathilde@example.fr'),
                    array('Coste', 'Jeanne', 'coste.jeanne@example.net'),
                    array('Hamon', 'Constance', 'hamon.constance@example.com'),
                    array('Guilbert', 'Alexandria', 'guilbert.alexandria@example.fr'),
                    array('Gautier', 'edouard', 'gautier.edouard@example.fr'),
                    array('Salmon', 'Emmanuelle', 'salmon.emmanuelle@example.com'),
                    array('Guillaume', 'Sylvie', 'guillaume.sylvie@example.fr'),
                    array('Lefevre', 'Martin', 'lefevre.martin@example.net'),
                    array('Vidal', 'Thibaut', 'vidal.thibaut@example.fr'),
                    array('Huet', 'Aime', 'huet.aime@example.com'),
                    array('Leleu', 'Alex', 'leleu.alex@example.fr'),
                    array('Delmas', 'Rene', 'delmas.rene@example.fr'),
                    array('Besnard', 'Jules', 'besnard.jules@example.fr'),
                    array('Prevost', 'Odette', 'prevost.odette@example.fr'),
                    array('Dos Santos', 'Marc', 'dos santos.marc@example.com'),
                    array('Bourgeois', 'Charlotte', 'bourgeois.charlotte@example.com'),
                    array('Grenier', 'Guillaume', 'grenier.guillaume@example.fr'),
                    array('Petitjean', 'Monique', 'petitjean.monique@example.com'),
                    array('Cousin', 'Amelie', 'cousin.amelie@example.com'),
                    array('Paris', 'Claude', 'paris.claude@example.fr'),
                    array('Rousseau', 'Martine', 'rousseau.martine@example.fr'),
                    array('Paris', 'Eugene', 'paris.eugene@example.com'),
                    array('Lenoir', 'eric', 'lenoir.eric@example.net'),
                    array('Alves', 'Noemi', 'alves.noemi@example.net'),
                    array('Robin', 'Charlotte', 'robin.charlotte@example.fr'),
                    array('Joseph', 'Andree', 'joseph.andree@example.net'),
                    array('Martel', 'Susan', 'martel.susan@example.com'),
                    array('Grenier', 'Nicole', 'grenier.nicole@example.com'),
                    array('Rey', 'Celina', 'rey.celina@example.fr'),
                    array('Raymond', 'Patrick', 'raymond.patrick@example.fr'),
                    array('Clement', 'Penelope', 'clement.penelope@example.fr'),
                    array('Nguyen', 'Stephanie', 'nguyen.stephanie@example.fr'),
                    array('Olivier', 'Josephine', 'olivier.josephine@example.fr'),
                    array('Imbert', 'Sabine', 'imbert.sabine@example.com'),
                    array('Chartier', 'Nath', 'chartier.nath@example.fr'),
                    array('Rey', 'Alix', 'rey.alix@example.fr'),
                    array('Germain', 'Monique', 'germain.monique@example.fr'),
                    array('Gilbert', 'Valentine', 'gilbert.valentine@example.fr'),
                    array('Moulin', 'Luc', 'moulin.luc@example.fr'),
                    array('Lenoir', 'Julie', 'lenoir.julie@example.com'),
                    array('Simon', 'Veronique', 'simon.veronique@example.fr'),
                    array('Adam', 'Marcelle', 'adam.marcelle@example.fr'),
                    array('Boyer', 'Gilles', 'boyer.gilles@example.com'),
                    array('Hoarau', 'Suzanne', 'hoarau.suzanne@example.fr'),
                    array('Dumont', 'Aime', 'dumont.aime@example.net'),
                    array('Barthelemy', 'Anouk', 'barthelemy.anouk@example.fr'),
                    array('Perrot', 'Louise', 'perrot.louise@example.fr'),
                    array('Ruiz', 'Matthieu', 'ruiz.matthieu@example.com'),
                    array('Lemaitre', 'Thibaut', 'lemaitre.thibaut@example.fr'),
                    array('Rodrigues', 'Daniel', 'rodrigues.daniel@example.com'),
                    array('Lelievre', 'Catherine', 'lelievre.catherine@example.fr'),
                    array('Boulay', 'Chantal', 'boulay.chantal@example.fr'),
                    array('Descamps', 'Camille', 'descamps.camille@example.fr'),
                    array('Marion', 'Marianne', 'marion.marianne@example.fr'),
                    array('Dupont', 'Tristan', 'dupont.tristan@example.fr'),
                    array('Fernandez', 'edouard', 'fernandez.edouard@example.fr'),
                    array('Delmas', 'Clemence', 'delmas.clemence@example.fr'),
                    array('Germain', 'Antoine', 'germain.antoine@example.net'),
                    array('Toussaint', 'Francoise', 'toussaint.francoise@example.com'),
                    array('Gerard', 'Guillaume', 'gerard.guillaume@example.com'),
                    array('Brunet', 'Hortense', 'brunet.hortense@example.com'),
                    array('Gallet', 'Paulette', 'gallet.paulette@example.net'),
                    array('Bruneau', 'Thierry', 'bruneau.thierry@example.fr'),
                    array('Chauvet', 'Sylvie', 'chauvet.sylvie@example.fr'),
                    array('Gosselin', 'Henriette', 'gosselin.henriette@example.fr'),
                    array('Lacombe', 'Eugene', 'lacombe.eugene@example.fr'),
                    array('Riou', 'Thibault', 'riou.thibault@example.fr'),
                    array('Duhamel', 'Patrick', 'duhamel.patrick@example.fr'),
                    array('Imbert', 'Alice', 'imbert.alice@example.fr'),
                    array('Hardy', 'Valerie', 'hardy.valerie@example.fr'),
                    array('Roussel', 'Margot', 'roussel.margot@example.fr'),
                    array('Riou', 'Louis', 'riou.louis@example.net'),
                    array('Gomes', 'edith', 'gomes.edith@example.fr'),
                    array('Le Goff', 'Marthe', 'le goff.marthe@example.com'),
                    array('Aubert', 'Patricia', 'aubert.patricia@example.com'),
                    array('Thierry', 'Honore', 'thierry.honore@example.com'),
                    array('Reynaud', 'Zacharie', 'reynaud.zacharie@example.fr',),
                    array('Leclercq', 'Louise', 'leclercq.louise@example.fr',),
                    array('De Oliveira', 'Martin', 'de oliveira.martin@example.fr',),
                    array('Evrard', 'eric', 'evrard.eric@example.com',),
                    array('Boucher', 'Patrick', 'boucher.patrick@example.fr',),
                    array('Gimenez', 'Leon', 'gimenez.leon@example.net',),
                    array('Salmon', 'Audrey', 'salmon.audrey@example.com',),
                    array('Gerard', 'Arthur', 'gerard.arthur@example.fr',),
                    array('Goncalves', 'Arnaude', 'goncalves.arnaude@example.com',),
                    array('Merle', 'Theophile', 'merle.theophile@example.fr',),
                    array('Techer', 'Gabrielle', 'techer.gabrielle@example.net',),
                    array('Charpentier', 'Marie', 'charpentier.marie@example.fr',),
                    array('Muller', 'Raymond', 'muller.raymond@example.com',),
                    array('Michaud', 'Manon', 'michaud.manon@example.fr',),
                    array('Olivier', 'Penelope', 'olivier.penelope@example.fr',),
                    array('Bernier', 'Tristan', 'bernier.tristan@example.fr',),
                    array('Arnaud', 'Monique', 'arnaud.monique@example.net',),
                    array('Salmon', 'Margot', 'salmon.margot@example.net',),
                    array('Delattre', 'Aurore', 'delattre.aurore@example.fr',),
                    array('Breton', 'Eugene', 'breton.eugene@example.fr',),
                    array('Costa', 'Dominique', 'costa.dominique@example.com',),
                    array('Imbert', 'Theodore', 'imbert.theodore@example.com',),
                    array('Marie', 'Odette', 'marie.odette@example.fr',),
                    array('Herve', 'Richard', 'herve.richard@example.fr',),
                    array('Fouquet', 'Manon', 'fouquet.manon@example.fr',),
                    array('Blanchard', 'Zoe', 'blanchard.zoe@example.net',),
                    array('Vasseur', 'etienne', 'vasseur.etienne@example.fr',),
                    array('Cohen', 'Denis', 'cohen.denis@example.com',),
                    array('Teixeira', 'Matthieu', 'teixeira.matthieu@example.com',),
                    array('Marechal', 'Laurent', 'marechal.laurent@example.fr',),
                    array('Hardy', 'Audrey', 'hardy.audrey@example.fr',),
                    array('Hubert', 'Danielle', 'hubert.danielle@example.net',),
                    array('Lenoir', 'Valentine', 'lenoir.valentine@example.net',),
                    array('Perrin', 'Gregoire', 'perrin.gregoire@example.fr',),
                    array('Fischer', 'Xavier', 'fischer.xavier@example.fr',),
                    array('Parent', 'Christelle', 'parent.christelle@example.com',),
                    array('Guillaume', 'Lucy', 'guillaume.lucy@example.fr',),
                    array('Couturier', 'Sophie', 'couturier.sophie@example.net',),
                    array('Roy', 'Auguste', 'roy.auguste@example.fr',),
                    array('Jacques', 'Ines', 'jacques.ines@example.fr',),
                    array('David', 'Remy', 'david.remy@example.fr',),
                    array('Benard', 'Virginie', 'benard.virginie@example.net',),
                    array('Pineau', 'Luc', 'pineau.luc@example.fr',),
                    array('Guichard', 'Daniel', 'guichard.daniel@example.fr',),
                    array('Buisson', 'Thibaut', 'buisson.thibaut@example.fr',),
                    array('Turpin', 'Valerie', 'turpin.valerie@example.net',),
                    array('Maillot', 'Christophe', 'maillot.christophe@example.fr',),
                    array('Lecoq', 'Maggie', 'lecoq.maggie@example.com',),
                    array('Marin', 'Pierre', 'marin.pierre@example.fr',),
                    array('Costa', 'Victor', 'costa.victor@example.fr',),
                    array('Peron', 'Chantal', 'peron.chantal@example.net',),
                    array('Renaud', 'elise', 'renaud.elise@example.fr',),
                    array('Langlois', 'Chantal', 'langlois.chantal@example.net',),
                    array('Thibault', 'Christiane', 'thibault.christiane@example.com',),
                    array('Pasquier', 'Hélene', 'pasquier.helene@example.fr',),
                    array('Le Roux', 'Monique', 'le roux.monique@example.net',),
                    array('Legros', 'Constance', 'legros.constance@example.fr',),
                    array('Vidal', 'Marianne', 'vidal.marianne@example.net',),
                    array('Martins', 'Isaac', 'martins.isaac@example.com',),
                    array('Rodrigues', 'Josette', 'rodrigues.josette@example.fr',),
                    array('Jacques', 'Andre', 'jacques.andre@example.com',),
                    array('Pires', 'Thibaut', 'pires.thibaut@example.fr',),
                    array('Alves', 'Paul', 'alves.paul@example.net',),
                    array('Morvan', 'Nath', 'morvan.nath@example.fr',),
                    array('Imbert', 'Celine', 'imbert.celine@example.fr',),
                    array('Morel', 'Veronique', 'morel.veronique@example.fr',),
                    array('Millet', 'eric', 'millet.eric@example.com',),
                    array('Clerc', 'Thibaut', 'clerc.thibaut@example.com',),
                    array('Ferreira', 'Christophe', 'ferreira.christophe@example.com',),
                    array('Blondel', 'Victor', 'blondel.victor@example.fr',),
                    array('Peltier', 'Marc', 'peltier.marc@example.net',),
                    array('Brun', 'Raymond', 'brun.raymond@example.fr',),
                    array('Rousseau', 'Thibault', 'rousseau.thibault@example.fr',),
                    array('Petitjean', 'Inès', 'petitjean.ines@example.fr',),
                    array('Bruneau', 'Olivie', 'bruneau.olivie@example.fr',),
                )
            ),
        );
    }
}
