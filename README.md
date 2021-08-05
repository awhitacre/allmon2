# Intro
This is a fork of Tim Sawyer, WD6AWP's excellent Allmon2 software, which provides a web UI for controlling Allstar nodes. This fork uses Bootstrap to provide a newer UI experience that is intended to work well on both desktop and mobile devices. There are also several changes implemented or planned that aim to make the user experience more intuitive, such as having a separate connection control form for each node (if multiple nodes are displayed on one screen), and a disconnect button next to each node in the connected nodes table.

# Development Status
This fork is still very much in development. Several features are not working correctly or are completely broken. I do **not** recommend using it in a production environment in its current state.

Also, I am not good at PHP, so I am trying to avoid making changes to that aspect of the app as much as possible. I feel like the server-side functionality of the app is excellent, so I am really just focusing on UI improvements, which don't require messing with the existing PHP too much.

# Known issues
- I haven't worked out the best way to hide/show the disconnect button based on user login status, so the button appears all the time for now. Of course, you still have to be logged in to disconnect nodes.
- The Control Panel function is completely broken/inaccessible right now. I am still working out the best way to implement it, since the old behavior (creating a popup window) doesn't work well for mobile devices.
- The Disconnect button is hard-coded to issue a "permanent disconnect" command regardless of whether the node is in a permanent or normal connection state. I don't know if this is really an issue or not (i.e., whether or not app_rpt cares, or if there is some disadvantage to doing it this way). The reason for this is because previously, this decision was left to the user via a checkbox. But IMO the user probably won't remember whether they need to do a permanent disconnect or not, since there is no way to tell from the UI what type of connection the node has. I found myself always checking the permanent box before a disconnect command just to be safe. If there is some way to determine the current connection type in the app, then the best thing to do is obviously to use that information to issue the correct type of disconnect command. For now, it seems to work though.
- Bootstrap is currently sourced from an external CDN. This may be better listed as a planned improvement rather than a bug. I did it this way to expedite development, but eventually the necessary libraries should be self-contained so that, among other reasons, the app can be run on a system/network with no Internet access (or slow/data-capped Internet).

# Planned improvements
- Re-implementation of the control panel in some more mobile-friendly form.
- Ability to define macros that issue commands across several nodes in sequence, in order to put several nodes into a desired, repeatable state at the click of one button. This could be useful for nets or other purposes.
- Dashboard screens with configurable cards that show controls (such as macros above) and other pieces of information.
