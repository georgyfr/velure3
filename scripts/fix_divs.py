"""
Fix all broken div nesting in front-page.html.

Pattern in EVERY product card, blog card, and instagram item:
  <div class="parent">
    <div class="image">
      <div style="...">
        <img .../>
      </div>
        <span style="color:...">Name</span>    ← ORPHAN (remnant of skeleton replacement)
      </div>                                      ← PREMATURE CLOSE (makes info/meta fall outside parent)
    <div class="info">...</div>
  </div>                                            ← EXTRA (no matching open)

Fix: Remove the orphan <span> line AND the premature </div> for each.
"""

import re

with open('/home/z/my-project/velure3/templates/front-page.html', 'r') as f:
    lines = f.readlines()

# Strategy: find all lines that match the orphan span pattern:
#   <span style="color:#...;font-size:12px;">...</span>
# These are ALWAYS followed by a </div> that prematurely closes the parent.
# And the line BEFORE the span is </div> that closes the inner style div.

orphan_pattern = re.compile(r'^\s+<span\s+style="color:#[0-9a-fA-F]+;font-size:12px;">.+</span>\s*$')

fixed = 0
i = 0
new_lines = []

while i < len(lines):
    line = lines[i]

    # Check if this line is an orphan span
    if orphan_pattern.match(line):
        # Verify: previous non-empty line should be </div> (closing inner style div)
        # And next line should be </div> (the premature close)
        prev_nonempty = None
        for j in range(i - 1, -1, -1):
            if lines[j].strip():
                prev_nonempty = j
                break

        next_nonempty = None
        for j in range(i + 1, len(lines)):
            if lines[j].strip():
                next_nonempty = j
                break

        # Verify the pattern
        if (prev_nonempty is not None and '</div>' in lines[prev_nonempty] and
            next_nonempty is not None and lines[next_nonempty].strip() == '</div>'):

            # Skip this orphan span line
            print(f"REMOVING orphan span at line {i+1}: {line.strip()[:80]}")
            fixed += 1
            i += 1  # move to next line

            # Also skip the premature </div> on the next non-empty line
            if lines[i].strip() == '</div>':
                print(f"REMOVING premature </div> at line {i+1}")
                fixed += 1
                i += 1
                continue
        else:
            # Pattern doesn't match exactly, keep the line
            new_lines.append(line)
    else:
        new_lines.append(line)

    i += 1

# Write the fixed file
with open('/home/z/my-project/velure3/templates/front-page.html', 'w') as f:
    f.writelines(new_lines)

print(f"\nTotal fixes applied: {fixed} lines removed")

# Verify the fix
content = ''.join(new_lines)
div_opens = content.count('<div')
div_closes = content.count('</div>')
print(f"New div balance: {div_opens} opens vs {div_closes} closes")
if div_opens == div_closes:
    print("✅ DIV BALANCE IS NOW PERFECT")
else:
    diff = div_closes - div_opens
    print(f"❌ Still {diff} extra </div> remaining")