<?php
/**
 * Plexis Content Management Asp
 *
 * @file        system/framework/IO/File.php
 * @copyright   2013, Plexis Dev Team
 * @license     GNU GPL v3
 */
namespace System\IO;
use FileNotFoundException;
use IOException;
use InvalidArgumentException;
use System\Collections\ListObject;

/**
 * Provides static methods for various file operations
 *
 * @author      Steven Wilson 
 * @package     Asp
 * @subpackage  IO
 */
class File
{
    /**
     * Creates a new file to the path specified
     *
     * @param string $sPath The full file path, including filename, of the
     *		file we are creating
     * @param bool $bReturnStream Return the FileStream for reading/writing?
     *
     * @throws IOException Thrown this method is unable to create the file
     *
     * @return \System\IO\FileStream|void
     */
    public static function Create($sPath, $bReturnStream = false)
    {
        $Stream = new FileStream($sPath);
        if($bReturnStream)
            return $Stream;
        else
            $Stream->close();

        return null;
    }

    /**
     * Returns whether a file path exists or not.
     *
     * @param string $sPath The full file path, including filename, of the
     *		file we are checking for
     *
     * @return bool
     */
    public static function Exists($sPath)
    {
        return file_exists($sPath);
    }

    /**
     * Removes a file from the filesystem
     *
     * @param string $path The full file path, including filename, of the
     *		file we are removing
     *
     * @return bool
     */
    public static function Delete($path)
    {
        return @unlink($path);
    }

    /**
     * Opens a FileStream on the specified path with read/write access
     *
     * @param string $sFilePath The full path, including file name to the file.
     *
     * @throws FileNotFoundException Thrown if the file does not exist
     * @throws IOException Thrown if there was an error opening the file.
     *
     * @return \System\IO\FileStream
     */
    public static function Open($sFilePath)
    {
        return new FileStream($sFilePath, FileStream::READWRITE);
    }

    /**
     * Opens a FileStream on the specified path with write access
     *
     * @param string $filePath The full path, including file name to the file.
     *
     * @throws FileNotFoundException Thrown if the file does not exist
     * @throws IOException Thrown if there was an error opening the file.
     *
     * @return \System\IO\FileStream
     */
    public static function OpenWrite($filePath)
    {
        return new FileStream($filePath, FileStream::WRITE);
    }

    /**
     * Opens a FileStream on the specified path with read access
     *
     * @param string $filePath The full path, including file name to the file.
     *
     * @throws FileNotFoundException Thrown if the file does not exist
     * @throws IOException Thrown if there was an error opening the file.
     *
     * @return \System\IO\FileStream
     */
    public static function OpenRead($filePath)
    {
        return new FileStream($filePath, FileStream::READ);
    }

    /**
     * Appends lines to a file
     *
     * If the specified file does not exist, this method creates a file,
     * and writes the specified lines to the file.
     *
     * @param string $filePath The full path, including file name to the file.
     * @param string[] $lines An array of lines to write to the file.
     *
     * @throws \IOException Thrown if there was an error opening, or creating the file.
     * @throws \InvalidArgumentException Thrown if $lines is not an array, or ListObject
     *
     * @return bool Returns whether the operation was successful
     */
    public static function AppendAllLines($filePath, $lines)
    {
        if(!is_array($lines) && !($lines instanceof ListObject))
            throw new InvalidArgumentException("Second parameter must be an array, ". gettype($lines) ." given");
        return self::AppendAllText($filePath, implode(PHP_EOL, $lines));
    }

    /**
     * Appends string data to a file
     *
     * If the specified file does not exist, this method creates a file,
     * and writes the specified lines to the file.
     *
     * @param string $filePath The full path, including file name to the file.
     * @param string $stringData The data string to write to the file
     *
     * @throws IOException Thrown if there was an error opening, or creating the file.
     *
     * @return bool Returns whether the operation was successful
     */
    public static function AppendAllText($filePath, $stringData)
    {
        // Get filestream
        $File = new FileStream($filePath, FileStream::WRITE);

        // Write file contents
        $wrote = $File->write($stringData);
        $File->close();
        return $wrote !== false;
    }

    /**
     * Opens a file, and gets all the lines of the file
     *
     * @param string $filePath The full path, including file name to the file.
     *
     * @throws FileNotFoundException Thrown if the file does not exist
     * @throws IOException Thrown if there was an error opening the file.
     *
     * @return string[]
     */
    public static function ReadAllLines($filePath)
    {
        return explode("\n", str_replace("\r\n", "\n", self::ReadAllText($filePath)));
    }

    /**
     * Opens a file, and gets all data of the file
     *
     * @param string $filePath The full path, including file name to the file.
     *
     * @throws FileNotFoundException Thrown if the file does not exist
     * @throws IOException Thrown if there was an error opening the file.
     *
     * @return string[]
     */
    public static function ReadAllText($filePath)
    {
        if( !file_exists($filePath) )
            throw new FileNotFoundException("File \"{$filePath}\" does not exist");

        $File = new FileStream($filePath, FileStream::READ);
        $Contents = $File->read();
        $File->close();
        return $Contents;
    }

    /**
     * Creates or overwrites a file, amd writes the specified string array to the file
     *
     * @param string $filePath The full path, including file name to the file.
     * @param string[] $lines An array of lines to write to the file.
     *
     * @throws \IOException Thrown if there was an error opening, or creating the file.
     * @throws \InvalidArgumentException Thrown if $lines is not an array, or ListObject
     *
     * @return bool Returns whether the operation was successful
     */
    public static function WriteAllLines($filePath, $lines)
    {
        if(!is_array($lines))
            throw new InvalidArgumentException("Second parameter must be an array, ". gettype($lines) ." given");
        return self::WriteAllText($filePath, implode(PHP_EOL, $lines));
    }

    /**
     * Creates or overwrites a file, amd writes the specified string to the file
     *
     * @param string $filePath The full path, including file name to the file.
     * @param string $stringData The data string to write to the file
     *
     * @throws IOException Thrown if there was an error opening, or creating the file.
     *
     * @return bool Returns whether the operation was successful
     */
    public static function WriteAllText($filePath, $stringData)
    {
        // Get filestream
        $File = new FileStream($filePath, FileStream::WRITE);

        // Write file contents
        $File->truncate();
        $wrote = $File->write($stringData);
        $File->close();
        return $wrote !== false;
    }

    /**
     * Moves a source file to a destination file
     *
     * @param string $SourceFileName The full file path, including filename, of the
     *		file we are moving
     * @param string $DestFileName The full file path, including filename, of the
     *		file that will be created
     *
     * @throws InvalidArgumentException Thrown if any parameters are left null
     * @throws IOException Thrown if there was an error moving the file, or
     * 	 creating the destination file's directory if it did not exist
     *
     * @return void
     */
    public static function Move($SourceFileName, $DestFileName)
    {
        // Make sure we have a filename
        if(empty($SourceFileName) || empty($DestFileName))
            throw new InvalidArgumentException("Invalid file name passed");

        // Correct new path
        $newPath = dirname($DestFileName);

        // Make sure Dest directory exists
        if( !Directory::Exists($newPath) )
            Directory::CreateDirectory($newPath);

        // Create new file
        $File = new FileInfo($SourceFileName);
        $File->moveTo($DestFileName);
    }
}