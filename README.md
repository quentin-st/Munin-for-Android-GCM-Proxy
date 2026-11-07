# Munin-for-Android-GCM-Proxy
Symfony server application whose aim is to transmit alerts from munin server to Firebase

```
	+----------------+                                                      
	|  munin master  +--+                                                 
	+----------------+  |                                                 
	                    |                                                 
	+----------------+  |   +-----------+                                            
	|  munin master  +----> | GCM proxy |                        +----------------+  
	+----------------+  |   +-----+-----+                   +--> | Android device |  
	                    |         |                         |    +----------------+  
	+----------------+  |         v                         |                        
	|  munin master  +--+        ++-----------------+       |    +----------------+  
	+----------------+           | Firebase servers +----------> | Android device |  
	                             +------------------+       |    +----------------+  
	                                                        |                        
	                                                        |    +----------------+  
	                                                        +--> | Android device |  
	                                                             +----------------+
```
