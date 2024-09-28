<?php

namespace Covaleski\Helpers\Enums;

/**
 * Special write modes.
 */
enum WriteMode
{
    /**
     * Add contents to the end of the file.
     */
    case APPEND;

    /**
     * Ovewrites the file contents at the specified or current position.
     */
    case OVERWRITE;

    /**
     * Truncates the file to the specified or zero length.
     */
    case TRUNCATE;
}
