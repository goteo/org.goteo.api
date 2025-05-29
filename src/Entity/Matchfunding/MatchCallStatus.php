<?php

namespace App\Entity\Matchfunding;

enum MatchCallStatus: string
{
    /**
     * The MatchCall is still being configured by the managers.
     */
    case InEditing = 'in_editing';

    /**
     * The MatchCall is taking in MatchCallSubmissions from Projects.
     */
    case InCalling = 'in_calling';

    /**
     * The MatchCall is giving matchmade funding to the accepted Projects.
     */
    case InMatchmaking = 'in_matchmaking';

    /**
     * The MatchCall finished giving funds and closing actions can now be performed.
     */
    case ToClosed = 'to_closed';

    /**
     * The MatchCall is now fully finished and will remain as read-only.
     */
    case Closed = 'closed';
}
