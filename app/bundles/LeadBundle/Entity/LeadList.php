<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Entity\FormEntity;
use Mautic\LeadBundle\Form\Validator\Constraints\UniqueUserAlias;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class LeadList
 * @ORM\Table(name="lead_lists")
 * @ORM\Entity(repositoryClass="Mautic\LeadBundle\Entity\LeadListRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class LeadList extends FormEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"leadListDetails", "leadListList"})
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"leadListDetails", "leadListList"})
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"leadListDetails", "leadListList"})
     */
    private $alias;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"leadListDetails", "leadListList"})
     */
    private $description;

    /**
     * @ORM\Column(type="array")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"leadListDetails"})
     */
    private $filters;

    /**
     * @ORM\Column(name="is_global", type="boolean")
     * @Serializer\Expose
     * @Serializer\Since("1.0")
     * @Serializer\Groups({"leadListDetails"})
     */
    private $isGlobal = true;

    /**
     * @ORM\ManyToMany(targetEntity="Lead", fetch="EXTRA_LAZY", indexBy="id", cascade={"remove"})
     * @ORM\JoinTable(name="lead_lists_included_leads")
     */
    private $includedLeads;

    /**
     * @ORM\ManyToMany(targetEntity="Lead", fetch="EXTRA_LAZY", indexBy="id", cascade={"remove"})
     * @ORM\JoinTable(name="lead_lists_excluded_leads")
     */
    private $excludedLeads;

    /**
     * @var array Used to populate the IDs of included Leads
     */
    private $leads;

    public function __construct()
    {
        $this->includedLeads = new ArrayCollection();
        $this->excludedLeads = new ArrayCollection();
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank(
            array('message' => 'mautic.core.name.required')
        ));

        $metadata->addConstraint(new UniqueUserAlias(array(
            'field'   => 'alias',
            'message' => 'mautic.lead.list.alias.unique'
        )));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param integer $name
     * @return LeadList
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return integer
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return LeadList
     */
    public function setDescription($description)
    {
        $this->isChanged('description', $description);
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return LeadList
     */
    public function setFilters(array $filters)
    {
        $this->isChanged('filters', $filters);
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set isGlobal
     *
     * @param boolean $isGlobal
     * @return LeadList
     */
    public function setIsGlobal($isGlobal)
    {
        $this->isChanged('isGlobal', $isGlobal);
        $this->isGlobal = $isGlobal;

        return $this;
    }

    /**
     * Get isGlobal
     *
     * @return boolean
     */
    public function getIsGlobal()
    {
        return $this->isGlobal;
    }

    /**
     * Proxy function to getIsGlobal()
     *
     * @return bool
     */
    public function isGlobal()
    {
        return $this->getIsGlobal();
    }

    /**
     * Set alias
     *
     * @param string $alias
     * @return LeadList
     */
    public function setAlias($alias)
    {
        $this->isChanged('alias', $alias);
        $this->alias = $alias;

        return $this;
    }

    /**
     * Get alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Add lead
     *
     * @param \Mautic\LeadBundle\Entity\Lead $lead

     */
    public function addLead(\Mautic\LeadBundle\Entity\Lead $lead, $checkAgainstExcluded = false)
    {
        if ($checkAgainstExcluded) {
            if (!$this->excludedLeads->contains($lead)) {
                $this->includedLeads[] = $lead;
                return true;
            }
            return false;
        } else {
            $this->includeLead($lead);
        }

        return $this;
    }

    /**
     * Remove lead
     *
     * @param \Mautic\LeadBundle\Entity\Lead $users
     */
    public function removeLead(\Mautic\LeadBundle\Entity\Lead $lead, $checkAgainstExcluded = false)
    {
        if ($checkAgainstExcluded) {
            //if the lead is in the excluded list, it was manually added there so don't remove it
            if (!$this->excludedLeads->contains($lead) && $this->includedLeads->contains($lead)) {
                $this->includedLeads->removeElement($lead);
                return true;
            }
            return false;
        } else {
            $this->excludeLead($lead);
        }

        return $this;
    }

    /**
     * Get manual leads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIncludedLeads()
    {
        return $this->includedLeads;
    }

    /**
     * Add lead
     *
     * @param \Mautic\LeadBundle\Entity\Lead $lead
     */
    public function excludeLead(\Mautic\LeadBundle\Entity\Lead $lead)
    {
        if (!$this->excludedLeads->contains($lead)) {
            $this->excludedLeads[] = $lead;
        }

        if ($this->includedLeads->contains($lead)) {
            $this->includedLeads->removeElement($lead);
        }
    }

    /**
     * @param Lead $lead
     */
    public function includeLead(\Mautic\LeadBundle\Entity\Lead $lead)
    {
        if (!$this->includedLeads->contains($lead)) {
            $this->includedLeads[] = $lead;
        }

        if ($this->excludedLeads->contains($lead)) {
            $this->excludedLeads->removeElement($lead);
        }
    }

    /**
     * Get manual leads
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExcludedLeads()
    {
        return $this->excludedLeads;
    }

    /**
     * @return array
     */
    public function getLeads ()
    {
        return $this->leads;
    }

    /**
     * @param array $leads
     */
    public function setLeads (array $leads)
    {
        $this->leads = $leads;
    }

    public function setIncludedLeads($leads)
    {
        $this->includedLeads = $leads;
    }

    public function setExcludedLeads($leads)
    {
        $this->excludedLeads = $leads;
    }
}
