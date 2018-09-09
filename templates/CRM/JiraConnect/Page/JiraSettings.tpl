<h3>Connected JIRA Instance</h3>


{if $connected}
    <p>You are connected!</p>
    <!-- TODO: disconnect -->
{else}
    <p>Please authorise a JIRA instance</p>
    <p><a href="{$oauth_url}">Connect</a></p>
{/if}

{* Example: Display a translated string -- which happens to include a variable *}
<p>{ts 1=$currentTime}(In your native language) The current time is %1.{/ts}</p>
