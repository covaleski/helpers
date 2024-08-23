<?php

namespace Covaleski\Helpers\Enums;

/**
 * File access modes.
 */
enum FileMode: string
{
    /**
     * Open for reading only; Place the file pointer at the beginning of the
     * file.
     */
    case READ_IF_EXISTS = 'r';

    /**
     * Open for reading and writing; place the file pointer at the beginning
     * of the file.
     */
    case READ_WRITE_IF_EXISTS = 'r+';

    /**
     * Open for writing only; place the file pointer at the beginning of the
     * file and truncate the file to zero length. If the file does not exist,
     * attempt to create it.
     */
    case WRITE_TRUNC = 'w';

    /**
     * Open for reading and writing; place the file pointer at the beginning
     * of the file and truncate the file to zero length. If the file does not
     * exist, attempt to create it.
     */
    case READ_WRITE_TRUNC = 'w+';

    /**
     * Open for writing only; place the file pointer at the end of the file.
     * If the file does not exist, attempt to create it. In this mode, fseek()
     * has no effect, writes are always appended.
     */
    case WRITE_APPEND = 'a';

    /**
     * Open for reading and writing; place the file pointer at the end of the
     * file. If the file does not exist, attempt to create it. In this mode,
     * fseek() has no effect, writes are always appended.
     */
    case READ_WRITE_APPEND = 'a+';

    /**
     * Create and open for writing only; place the file pointer at the
     * beginning of the file. If the file already exists, the fopen() call will
     * fail by returning false and generating an error of level E_WARNING. If
     * the file does not exist, attempt to create it. This is equivalent to
     * specifying O_EXCL|O_CREAT flags for the underlying open(2) system call. 
     */
    case WRITE_IF_NOT_EXISTS = 'x';

    /**
     * Create reading and writing; place the file pointer at the
     * beginning of the file. If the file already exists, the fopen() call will
     * fail by returning false and generating an error of level E_WARNING. If
     * the file does not exist, attempt to create it. This is equivalent to
     * specifying O_EXCL|O_CREAT flags for the underlying open(2) system call. 
     */
    case READ_WRITE_IF_NOT_EXISTS = 'x+';

    /**
     * Open the file for writing only. If the file does not exist, it is
     * created. If it exists, it is neither truncated (as opposed to 'w'),
     * nor the call to this function fails (as is the case with 'x'). The file
     * pointer is positioned on the beginning of the file. This may be useful
     * if it's desired to get an advisory lock (see flock()) before attempting
     * to modify the file, as using 'w' could truncate the file before the lock
     * was obtained (if truncation is desired, ftruncate() can be used after
     * the lock is requested). 
     */
    case WRITE = 'c';

    /**
     * Open the file for reading and writing. If the file does not exist, it is
     * created. If it exists, it is neither truncated (as opposed to 'w'),
     * nor the call to this function fails (as is the case with 'x'). The file
     * pointer is positioned on the beginning of the file. This may be useful
     * if it's desired to get an advisory lock (see flock()) before attempting
     * to modify the file, as using 'w' could truncate the file before the lock
     * was obtained (if truncation is desired, ftruncate() can be used after
     * the lock is requested). 
     */
    case READ_WRITE = 'c+';

    /**
     * Set close-on-exec flag on the opened file descriptor. Only available in
     * PHP compiled on POSIX.1-2008 conform systems.
     */
    case CLOSE_ON_EXEC = 'e';
}
