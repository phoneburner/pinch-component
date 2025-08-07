<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Domain;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ContentTypeTest extends TestCase
{
    #[Test]
    #[DataProvider('provideConstants')]
    public function constants(string $expected, string $actual): void
    {
        self::assertSame($expected, $actual);
    }

    public static function provideConstants(): iterable
    {
        yield 'JSON' => ['application/json', ContentType::JSON];
        yield 'HAL_JSON' => ['application/hal+json', ContentType::HAL_JSON];
        yield 'HEALTH_JSON' => ['application/health+json', ContentType::HEALTH_JSON];
        yield 'PNG' => ['image/png', ContentType::PNG];
        yield 'HTML' => ['text/html', ContentType::HTML];
        yield 'PROBLEM_DETAILS_JSON' => ['application/problem+json', ContentType::PROBLEM_DETAILS_JSON];
        yield 'TEXT' => ['text/plain', ContentType::TEXT];
        yield 'CSV' => ['text/csv', ContentType::CSV];
        yield 'OCTET_STREAM' => ['application/octet-stream', ContentType::OCTET_STREAM];
        yield 'ZIP' => ['application/zip', ContentType::ZIP];
        yield 'PHP' => ['application/x-php', ContentType::PHP];
        yield 'GIF' => ['image/gif', ContentType::GIF];
        yield 'CSS' => ['text/css', ContentType::CSS];
        yield 'JS' => ['text/javascript', ContentType::JS];
        yield 'AIFF' => ['audio/x-aiff', ContentType::AIFF];
        yield 'AVI' => ['video/avi', ContentType::AVI];
        yield 'BMP' => ['image/bmp', ContentType::BMP];
        yield 'BZ2' => ['application/x-bz2', ContentType::BZ2];
        yield 'DMG' => ['application/x-apple-diskimage', ContentType::DMG];
        yield 'DOC' => ['application/msword', ContentType::DOC];
        yield 'DOCX' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', ContentType::DOCX];
        yield 'JPEG' => ['image/jpeg', ContentType::JPEG];
        yield 'FLV' => ['video/x-flv', ContentType::FLV];
        yield 'GZ' => ['application/gzip', ContentType::GZ];
        yield 'EML' => ['message/rfc822', ContentType::EML];
        yield 'PS' => ['application/postscript', ContentType::PS];
        yield 'XML' => ['application/xml', ContentType::XML];
        yield 'XLSX' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ContentType::XLSX];
        yield 'WAV' => ['audio/wav', ContentType::WAV];
        yield 'XLS' => ['application/excel', ContentType::XLS];
        yield 'WMV' => ['audio/x-ms-wmv', ContentType::WMV];
        yield 'WMA' => ['audio/x-ms-wma', ContentType::WMA];
        yield 'VCF' => ['text/x-vcard', ContentType::VCF];
        yield 'TTF' => ['application/x-font-truetype', ContentType::TTF];
        yield 'TIFF' => ['image/tiff', ContentType::TIFF];
        yield 'SVG' => ['image/svg+xml', ContentType::SVG];
        yield 'SIT' => ['application/x-stuffit', ContentType::SIT];
        yield 'TAR' => ['application/x-tar', ContentType::TAR];
        yield 'RTF' => ['application/rtf', ContentType::RTF];
        yield 'RAR' => ['application/x-rar-compressed', ContentType::RAR];
        yield 'PPTX' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', ContentType::PPTX];
        yield 'PPT' => ['application/vnd.ms-powerpoint', ContentType::PPT];
        yield 'PDF' => ['application/pdf', ContentType::PDF];
        yield 'OGG' => ['audio/ogg', ContentType::OGG];
        yield 'ODS' => ['application/vnd.oasis.opendocument.spreadsheet', ContentType::ODS];
        yield 'ODT' => ['application/vnd.oasis.opendocument.text', ContentType::ODT];
        yield 'ODP' => ['application/vnd.oasis.opendocument.presentation', ContentType::ODP];
        yield 'ODG' => ['application/vnd.oasis.opendocument.graphics', ContentType::ODG];
        yield 'MP3' => ['audio/mpeg', ContentType::MP3];
        yield 'MP4' => ['video/mp4', ContentType::MP4];
        yield 'MPEG' => ['video/mpeg', ContentType::MPEG];
        yield 'MOV' => ['video/quicktime', ContentType::MOV];
        yield 'MIDI' => ['audio/midi', ContentType::MIDI];
        yield 'EXE' => ['application/x-ms-dos-executable', ContentType::EXE];
        yield 'HQX' => ['application/stuffit', ContentType::HQX];
        yield 'JAR' => ['application/x-java-archive', ContentType::JAR];
        yield 'M3U' => ['audio/x-mpegurl', ContentType::M3U];
        yield 'M4A' => ['audio/mp4', ContentType::M4A];
        yield 'MDB' => ['application/x-msaccess', ContentType::MDB];
        yield 'ICO' => ['image/x-icon', ContentType::ICO];
        yield 'YAML' => ['application/yaml', ContentType::YAML];
        yield 'ICS' => ['text/calendar', ContentType::ICS];
    }

    #[Test]
    #[DataProvider('provideMimeTypeLookups')]
    public function lookupReturnsCorrectMimeType(string $extension, string $expectedMimeType): void
    {
        self::assertSame($expectedMimeType, ContentType::lookup($extension));
    }

    #[Test]
    #[DataProvider('provideUnknownExtensions')]
    public function lookupReturnsNullForUnknownExtensions(string $extension): void
    {
        self::assertNull(ContentType::lookup($extension));
    }

    #[Test]
    #[DataProvider('provideCaseSensitiveExtensions')]
    public function lookupIsCaseSensitive(string $extension): void
    {
        self::assertNull(ContentType::lookup($extension));
    }

    public static function provideMimeTypeLookups(): iterable
    {
        yield 'json' => ['json', ContentType::JSON];
        yield 'html' => ['html', ContentType::HTML];
        yield 'htm' => ['htm', ContentType::HTML];
        yield 'png' => ['png', ContentType::PNG];
        yield 'jpg' => ['jpg', ContentType::JPEG];
        yield 'jpeg' => ['jpeg', ContentType::JPEG];
        yield 'pdf' => ['pdf', ContentType::PDF];
        yield 'zip' => ['zip', ContentType::ZIP];
        yield 'php' => ['php', ContentType::PHP];
        yield 'php3' => ['php3', ContentType::PHP];
        yield 'php4' => ['php4', ContentType::PHP];
        yield 'php5' => ['php5', ContentType::PHP];
        yield 'bin' => ['bin', ContentType::OCTET_STREAM];
        yield 'so' => ['so', ContentType::OCTET_STREAM];
        yield 'o' => ['o', ContentType::OCTET_STREAM];
        yield 'a' => ['a', ContentType::OCTET_STREAM];
        yield 'doc' => ['doc', ContentType::DOC];
        yield 'docx' => ['docx', ContentType::DOCX];
        yield 'xls' => ['xls', ContentType::XLS];
        yield 'xlsx' => ['xlsx', ContentType::XLSX];
        yield 'ppt' => ['ppt', ContentType::PPT];
        yield 'pptx' => ['pptx', ContentType::PPTX];
        yield 'mp3' => ['mp3', ContentType::MP3];
        yield 'mp4' => ['mp4', ContentType::MP4];
        yield 'wav' => ['wav', ContentType::WAV];
        yield 'sln' => ['sln', ContentType::WAV];
        yield 'ogg' => ['ogg', ContentType::OGG];
        yield 'mov' => ['mov', ContentType::MOV];
        yield 'gif' => ['gif', ContentType::GIF];
        yield 'css' => ['css', ContentType::CSS];
        yield 'js' => ['js', ContentType::JS];
        yield 'aif' => ['aif', ContentType::AIFF];
        yield 'aiff' => ['aiff', ContentType::AIFF];
        yield 'avi' => ['avi', ContentType::AVI];
        yield 'bmp' => ['bmp', ContentType::BMP];
        yield 'bz2' => ['bz2', ContentType::BZ2];
        yield 'csv' => ['csv', ContentType::CSV];
        yield 'dmg' => ['dmg', ContentType::DMG];
        yield 'eml' => ['eml', ContentType::EML];
        yield 'eps' => ['eps', ContentType::PS];
        yield 'ps' => ['ps', ContentType::PS];
        yield 'exe' => ['exe', ContentType::EXE];
        yield 'flv' => ['flv', ContentType::FLV];
        yield 'gz' => ['gz', ContentType::GZ];
        yield 'hqx' => ['hqx', ContentType::HQX];
        yield 'ics' => ['ics', ContentType::ICS];
        yield 'jar' => ['jar', ContentType::JAR];
        yield 'm3u' => ['m3u', ContentType::M3U];
        yield 'm4a' => ['m4a', ContentType::M4A];
        yield 'mdb' => ['mdb', ContentType::MDB];
        yield 'mid' => ['mid', ContentType::MIDI];
        yield 'midi' => ['midi', ContentType::MIDI];
        yield 'mpg' => ['mpg', ContentType::MPEG];
        yield 'mpeg' => ['mpeg', ContentType::MPEG];
        yield 'odg' => ['odg', ContentType::ODG];
        yield 'odp' => ['odp', ContentType::ODP];
        yield 'odt' => ['odt', ContentType::ODT];
        yield 'ods' => ['ods', ContentType::ODS];
        yield 'rar' => ['rar', ContentType::RAR];
        yield 'rtf' => ['rtf', ContentType::RTF];
        yield 'tar' => ['tar', ContentType::TAR];
        yield 'sit' => ['sit', ContentType::SIT];
        yield 'svg' => ['svg', ContentType::SVG];
        yield 'tif' => ['tif', ContentType::TIFF];
        yield 'tiff' => ['tiff', ContentType::TIFF];
        yield 'ttf' => ['ttf', ContentType::TTF];
        yield 'vcf' => ['vcf', ContentType::VCF];
        yield 'wma' => ['wma', ContentType::WMA];
        yield 'wmv' => ['wmv', ContentType::WMV];
        yield 'xml' => ['xml', ContentType::XML];
        yield 'ico' => ['ico', ContentType::ICO];
        yield 'yaml' => ['yaml', ContentType::YAML];
        yield 'yml' => ['yml', ContentType::YAML];
        yield 'bpk' => ['bpk', ContentType::OCTET_STREAM];
        yield 'deploy' => ['deploy', ContentType::OCTET_STREAM];
        yield 'dist' => ['dist', ContentType::OCTET_STREAM];
        yield 'distz' => ['distz', ContentType::OCTET_STREAM];
        yield 'dms' => ['dms', ContentType::OCTET_STREAM];
        yield 'dump' => ['dump', ContentType::OCTET_STREAM];
        yield 'elc' => ['elc', ContentType::OCTET_STREAM];
        yield 'lha' => ['lha', ContentType::OCTET_STREAM];
        yield 'lrf' => ['lrf', ContentType::OCTET_STREAM];
        yield 'lzh' => ['lzh', ContentType::OCTET_STREAM];
        yield 'obj' => ['obj', ContentType::OCTET_STREAM];
        yield 'pkg' => ['pkg', ContentType::OCTET_STREAM];
    }

    public static function provideUnknownExtensions(): iterable
    {
        yield 'unknown' => ['unknown'];
        yield 'empty' => [''];
        yield 'xyz' => ['xyz'];
    }

    public static function provideCaseSensitiveExtensions(): iterable
    {
        yield 'JSON' => ['JSON'];
        yield 'HTML' => ['HTML'];
        yield 'PNG' => ['PNG'];
    }
}
